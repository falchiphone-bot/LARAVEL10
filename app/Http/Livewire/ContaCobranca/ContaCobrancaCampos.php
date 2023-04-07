<?php

namespace App\Http\Livewire\ContaCobranca;

use App\Models\Conta;
use App\Models\DevSicredi;
use App\Models\Historicos;
use Livewire\Component;

class ContaCobrancaCampos extends Component
{
    public $contaCobranca;
    public $contaCobrancaID;
    public $historicoCredito;
    public $historicoDebito;
    public $empresa;
    public $d_tarifa;
    public $d_cobranca;

    public $historicoCreditoContaDebito;
    public $historicoCreditoContaCredito;
    public $historicoDebitoContaDebito;
    public $historicoDebitoContaCredito;

    //enviado via controller
    public $clientes;
    public $vendedores;
    public $produtos;

    protected $listeners = [
        'selectContaCredito',
        'selectContaDebito',
    ];

    public function selectContaCredito($value)
    {
        $this->historicoCredito = $value;
        $historico = Historicos::find($value);
        $this->historicoCreditoContaDebito = $historico->ContaDebito->PlanoConta->Descricao;
        $this->historicoCreditoContaCredito = $historico->ContaCredito->PlanoConta->Descricao;
    }
    public function selectContaDebito($value)
    {
        $this->historicoDebito = $value;
        $historico = Historicos::find($value);
        $this->historicoDebitoContaDebito = $historico->ContaDebito->PlanoConta->Descricao;
        $this->historicoDebitoContaCredito = $historico->ContaCredito->PlanoConta->Descricao;
    }

    public function hydrate()
    {
        $this->emit('select2');
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function mount($contaCobranca)
    {
        $this->contaCobranca = $contaCobranca;
        $this->historicoCredito = $contaCobranca->Credito_Cobranca;
        $this->historicoDebito = $contaCobranca->Tarifa_Cobranca;
        $this->d_cobranca = $contaCobranca->d_cobranca;
        $this->d_tarifa = $contaCobranca->d_tarifa;

        $this->empresa = $contaCobranca->Empresa->Descricao;
    }

    public function render()
    {
        $contasDev = DevSicredi::orderBy('DESENVOLVEDOR')->pluck('DESENVOLVEDOR','id');
        $historicos = Historicos::orderBy('Descricao')->where('EmpresaID',$this->contaCobranca->EmpresaID)->pluck('Descricao','ID');
        return view('livewire.conta-cobranca.conta-cobranca-campos',compact('contasDev','historicos'));
    }
}
