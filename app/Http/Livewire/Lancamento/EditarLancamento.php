<?php

namespace App\Http\Livewire\Lancamento;

use App\Models\Conta;
use App\Models\Empresa;
use App\Models\Historicos;
use App\Models\Lancamento;
use App\Models\LancamentoComentario;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EditarLancamento extends Component
{
    public $contas;
    public Lancamento $lancamento;
    public $historicos;
    public $comentarios;
    public $comentario;

    public $currentTab;

    protected $listeners = ['alterarIdLancamento'];

    public function alterarIdLancamento($lancamento_id)
    {
        $this->mount($lancamento_id);
    }

    protected $rules = [
        'lancamento.Valor' => 'required|numeric',
        'lancamento.ContaCreditoID' => 'required|integer',
        'lancamento.ContaDebitoID' => 'required|integer',
        'lancamento.DataContabilidade' => 'required|date',
        'lancamento.HistoricoID' => ['required_without:lancamento.Descricao'],
        'lancamento.Descricao' => 'required_without:lancamento.HistoricoID|string',
    ];

    public function salvarLancamento()
    {
        $this->validate();
        if (!$this->temBloqueio($this->lancamento->ID)) {
            $this->lancamento['DataContabilidade'] = $this->lancamento->DataContabilidade->format('d-m-Y');
            // dd($this->lancamento);
            if ($this->lancamento->save()) {
                session()->flash('message', 'Lançamento atualizado.');
                // $this->lancamento['DataContabilidade'] = $this->lancamento->DataContabilidade->format('Y-m-d');
            }else {
                $this->addError('save','Erro ao atualizar lançamento');
            }
        }
    }

    public function salvarComentario()
    {
        if ($this->comentario) {
            LancamentoComentario::create([
                'LancamentoID' => $this->lancamento->ID,
                'Descricao' => $this->comentario,
                'UsuarioID' => Auth::user()->id,
                'Created' => date('d/m/Y H:i:s'),
                'Visualizado' => 0
            ]);
            session()->flash('message', 'Comentário adicionado.');
        }else{
            $this->addError('save','Preecha comentário para salvar!');
        }
    }

    public function temBloqueio($lancamento_id)
    {
        $lancamento = Lancamento::find($lancamento_id);
        $dataLancamento = Carbon::createFromDate($lancamento->DataContabilidade);

        $data_conta_debito = $lancamento->ContaDebito->Bloqueiodataanterior;

        if ($data_conta_debito) {
            if ($data_conta_debito->greaterThanOrEqualTo($dataLancamento)) {
                $this->addError('data_bloqueio', 'Bloqueio de Data na Conta Debito');
                return true;
            }
        }
        $data_empresa = Empresa::find($lancamento->EmpresaID)->Bloqueiodataanterior;
        if ($data_empresa) {
            if ($data_empresa->greaterThanOrEqualTo($dataLancamento)) {
                $this->addError('data_bloqueio', 'Bloqueio de Data na Empresa');
                return true;
            }
        }
        if ($lancamento->ContaCredito->Bloqueiodataanterior) {
            if ($lancamento->ContaCredito->Bloqueiodataanterior->greaterThanOrEqualTo($dataLancamento)) {
                $this->addError('data_bloqueio', 'Bloqueio de Data na Conta Credito');
                return true;
            }
        }
        return false;
    }

    public function sessionTab($tab)
    {
        $this->currentTab = $tab;
    }

    public function selectHistorico()
    {
        $historico = Historicos::find($this->lancamento->HistoricoID);
        if ($historico) {
            $this->lancamento->ContaDebitoID = $historico->ContaDebitoID;
            $this->lancamento->ContaCreditoID = $historico->ContaCreditoID;
        }
    }

    public function mount($lancamento_id)
    {
        $this->currentTab = 'lancamento';

        $this->lancamento = Lancamento::find($lancamento_id);

        $this->comentarios = LancamentoComentario::where('LancamentoID',$lancamento_id)->get();

        $this->contas = Conta::where('EmpresaID',$this->lancamento->EmpresaID)->where('Grau',5)
        ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
        ->orderBy('PlanoContas.Descricao')->pluck('PlanoContas.Descricao', 'Contas.ID');
        $this->historicos = Historicos::where('EmpresaID',$this->lancamento->EmpresaID)->orderBy('Descricao')->pluck('Descricao','ID');
    }

    public function render()
    {
        return view('livewire.lancamento.editar-lancamento')->extends('layouts.bootstrap5');
    }
}
