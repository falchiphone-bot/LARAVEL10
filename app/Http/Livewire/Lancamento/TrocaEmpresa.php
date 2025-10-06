<?php

namespace App\Http\Livewire\Lancamento;

use App\Models\Conta;
use App\Models\Empresa;
use App\Models\Lancamento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TrocaEmpresa extends Component
{
    public $empresas;
    public $novaempresa;
    public $novacontadebito;
    public $novacontacredito;
    public $successMsg;
    public $contasnovas = [];

    public $lancamento_id;

    protected $listeners = ['setContaDebito','setContaCredito','setLancamentoID','refreshData'];

    public function setContaDebito($id)
    {
        $this->novacontadebito = $id;
    }
    public function setContaCredito($id)
    {
        $this->novacontacredito = $id;
    }
    public function setLancamentoID($id)
    {
        $this->lancamento_id = $id;
    }

    public function refreshData()
    {
        // Recarrega lista de empresas/contas e mantém seleção existente
        $this->mount(); // repopula $empresas
        if($this->novaempresa){
            $this->empresaSelecionada();
        }
        // Se já houver lançamento, garantir que id está setado
        if(!$this->lancamento_id && request()->has('lancamento_id')){
            $this->lancamento_id = (int)request()->get('lancamento_id');
        }
    }

    protected $rules = [
        'novaempresa' => 'required',
        'novacontadebito' => 'required',
        'novacontacredito' => 'required',
    ];

    public function empresaSelecionada()
    {
        $this->contasnovas = Conta::where('EmpresaID',$this->novaempresa)->where('Grau',5)
        ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
        ->orderBy('PlanoContas.Descricao')
        ->pluck('PlanoContas.Descricao', 'Contas.ID');

        // $this->emit('select2');
    }

    public function transferirLancamento()
    {
        $this->validate();
        if (!$this->temBloqueio($this->lancamento_id)) {
            $lancamento = Lancamento::find($this->lancamento_id);
            $lancamento->EmpresaID = $this->novaempresa;
            $lancamento->ContaDebitoID = $this->novacontadebito;
            $lancamento->ContaCreditoID = $this->novacontacredito;
            if ($lancamento->save()) {
                session()->flash('message','Lançamento atualizado');
                $this->emitTo('conta.extrato','trocaEmpresa');
            }else {
                $this->addError('save','Erro ao atualizar lançamento');
            }
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


    public function hydrate()
    {
        // $this->emit('select2');
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function mount()
    {
        // Carrega empresas do usuário
        $this->empresas = Empresa::join('Contabilidade.EmpresasUsuarios','EmpresasUsuarios.EmpresaID','Empresas.ID')
            ->where('EmpresasUsuarios.UsuarioID',Auth::user()->id)
            ->orderBy('Descricao')
            ->pluck('Descricao','Empresas.ID');
        // Se um lançamento foi definido antes do mount, tenta manter seleção padrão
        if($this->lancamento_id && !$this->novaempresa){
            $lanc = Lancamento::find($this->lancamento_id);
            if($lanc){
                $this->novaempresa = $lanc->EmpresaID;
                $this->empresaSelecionada();
            }
        }
    }
    public function render()
    {
        return view('livewire.lancamento.troca-empresa');
    }
}
