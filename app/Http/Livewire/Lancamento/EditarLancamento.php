<?php

namespace App\Http\Livewire\Lancamento;

use App\Models\Conta;
use App\Models\Empresa;
use App\Models\Lancamento;
use App\Models\LancamentoComentario;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EditarLancamento extends Component
{
    public $contas;
    public $successMsg = '';
    public Lancamento $lancamento;
    public $comentarios;
    public $comentario;

    protected $rules = [
        'lancamento.Descricao' => 'required|string|min:6',
        'lancamento.Valor' => 'required|numeric',
        'lancamento.ContaCreditoID' => 'required|integer',
        'lancamento.ContaDebitoID' => 'required|integer',
        'lancamento.DataContabilidade' => 'required|date',
    ];

    public function salvarLancamento()
    {
        $this->validate();
        if (!$this->temBloqueio($this->lancamento->ID)) {
            if ($this->lancamento->save()) {
                $this->successMsg = 'Lançamento atualizado';
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
            $this->successMsg = 'Comentário adicionado';
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

    public function mount($lancamento_id = null)
    {
        if ($lancamento_id) {
            $this->lancamento = Lancamento::find($lancamento_id);
        }
        $this->comentarios = LancamentoComentario::where('LancamentoID',$lancamento_id)->get();

        $this->contas = Conta::where('EmpresaID',$this->lancamento->EmpresaID)->where('Grau',5)
        ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
        ->orderBy('PlanoContas.Descricao')->pluck('PlanoContas.Descricao', 'Contas.ID');
    }

    public function render()
    {
        return view('livewire.lancamento.editar-lancamento')->extends('layouts.bootstrap5');
    }
}
