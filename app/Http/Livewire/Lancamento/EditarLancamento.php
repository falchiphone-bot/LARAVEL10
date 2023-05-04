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

    protected $listeners = ['alterarIdLancamento', 'salvarLancamento', 'selectHistorico','changeContaDebitoID','changeContaCreditoID'];

    public function alterarIdLancamento($lancamento_id)
    {
        $this->mount($lancamento_id);
    }

    protected $rules = [
        'lancamento.Valor' => 'required',
        'lancamento.ContaCreditoID' => 'required|integer',
        'lancamento.ContaDebitoID' => 'required|integer',
        'lancamento.DataContabilidade' => 'required|date',
        'lancamento.HistoricoID' => ['required_without:lancamento.Descricao'],
        'lancamento.Descricao' => 'required_without:lancamento.HistoricoID',
    ];

    public function changeContaDebitoID($value)
    {
        $this->lancamento->ContaDebitoID = $value;
    }

    public function changeContaCreditoID($value)
    {
        $this->lancamento->ContaCreditoID = $value;
    }

    public function salvarLancamento($novo = null)
    {
        $this->validate();
        if ($novo) {
            $novoLancamento = $this->lancamento->replicate();
            if (!$this->temBloqueio()) {
                $novoLancamento->DataContabilidade = $this->lancamento->DataContabilidade->format('d/m/Y');
                $novoLancamento->save();
                session()->flash('message', 'Lançamento Criado.');
            }
        } elseif (!$this->temBloqueio($this->lancamento->ID, $this->lancamento->DataContabilidade)) {
            $this->lancamento['DataContabilidade'] = $this->lancamento->DataContabilidade->format('d-m-Y');
            $this->lancamento['EmpresaID'] = $this->lancamento['EmpresaID']??session('conta.extrato.empresa.id');
            $this->lancamento['Usuarios_id'] = $this->lancamento['Usuarios_id']??Auth::user()->id;
            $this->lancamento['Valor'] = str_replace(',','.',str_replace('.','',$this->lancamento['Valor']));

            if ($this->lancamento->save()) {
                session()->flash('message', 'Lançamento atualizado.');
                // $this->lancamento['DataContabilidade'] = $this->lancamento->DataContabilidade->format('Y-m-d');
            } else {
                $this->addError('save', 'Erro ao atualizar lançamento');
            }
        }
    }

    public function acao($value)
    {
        $this->metodo = $value;
    }

    public function salvarComentario()
    {
        if ($this->comentario) {
            LancamentoComentario::create([
                'LancamentoID' => $this->lancamento->ID,
                'Descricao' => $this->comentario,
                'UsuarioID' => Auth::user()->id,
                'Created' => date('d/m/Y H:i:s'),
                'Visualizado' => 0,
            ]);
            session()->flash('message', 'Comentário adicionado.');
        } else {
            $this->addError('save', 'Preecha comentário para salvar!');
        }
    }

    public function temBloqueio($lancamento_id = null)
    {
        if ($lancamento_id) {
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
        } elseif ($this->lancamento->DataContabilidade) {
            $data_empresa = $data_empresa = Empresa::find(session('conta.extrato.empresa.id'))->Bloqueiodataanterior;
            if ($data_empresa) {
                if ($data_empresa->greaterThanOrEqualTo($this->lancamento->DataContabilidade)) {
                    $this->addError('data_bloqueio', 'Bloqueio de Data na Empresa');
                    return true;
                }
            }
            if ($this->lancamento->ContaDebito->Bloqueiodataanterior) {
                if ($this->lancamento->ContaDebito->Bloqueiodataanterior->greaterThanOrEqualTo($this->lancamento->DataContabilidade)) {
                    $this->addError('data_bloqueio', 'Bloqueio de Data na Conta Debito');
                    return true;
                }
            }
            if ($this->lancamento->ContaCredito->Bloqueiodataanterior) {
                if ($this->lancamento->ContaCredito->Bloqueiodataanterior->greaterThanOrEqualTo($this->lancamento->DataContabilidade)) {
                    $this->addError('data_bloqueio', 'Bloqueio de Data na Conta Credito');
                    return true;
                }
            }
        } else {
            return false;
        }
    }

    public function sessionTab($tab)
    {
        $this->currentTab = $tab;
    }

    public function selectHistorico($value)
    {
        $this->lancamento->HistoricoID = $value;
        $historico = Historicos::find($this->lancamento->HistoricoID);
        if ($historico) {
            $this->lancamento->ContaDebitoID = $historico->ContaDebitoID;
            $this->lancamento->ContaCreditoID = $historico->ContaCreditoID;
        }
        // $this->emitTo('conta.extrato','select2');
    }

    public function hydrate()
    {
        // $this->emit('select2');
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function mount($lancamento_id)
    {
        $this->emitTo('lancamento.troca-empresa','setLancamentoID',$lancamento_id);
        $this->currentTab = 'lancamento';

        if ($lancamento_id != 'novo') {
            $this->lancamento = Lancamento::find($lancamento_id);
            $this->lancamento->Valor = number_format($this->lancamento->Valor,2,',','.');
            $this->comentarios = LancamentoComentario::where('LancamentoID', $lancamento_id)->get();
        } else {
            $this->lancamento = new Lancamento();
        }

        $this->contas = Conta::where('EmpresaID', $this->lancamento->EmpresaID ?? session('conta.extrato.empresa.id'))
            ->where('Grau', 5)
            ->join('Contabilidade.PlanoContas', 'PlanoContas.ID', 'Planocontas_id')
            ->orderBy('PlanoContas.Descricao')
            ->pluck('PlanoContas.Descricao', 'Contas.ID');
        $this->historicos = Historicos::where('EmpresaID', $this->lancamento->EmpresaID ?? session('conta.extrato.empresa.id'))
            ->orderBy('Descricao', 'asc')
            ->get(['Descricao', 'ID']);
    }

    public function render()
    {
        return view('livewire.lancamento.editar-lancamento');
    }
}
