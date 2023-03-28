<?php

namespace App\Http\Livewire\Conta;

use App\Models\Conta;
use App\Models\Lancamento;
use Carbon\Carbon;
use Livewire\Component;

class Extrato extends Component
{
    public $Conta;
    public $Lancamentos;

    //configurações de pesquisa
    public $De;
    public $Ate;
    public $Descricao;
    public $DescricaoApartirDe;
    public $Conferido;
    public $Notificacao;
    public $DataBloqueio;


    public function mount($contaID)
    {
        $this->Conta = Conta::find($contaID);

        $lancamentos = Lancamento::where(function ($query) use ($contaID) {
            return $query->where('ContaDebitoID',$contaID)->orWhere('ContaCreditoID',$contaID);
        })->where('DataContabilidade',date('d/m/Y'));

        $this->Lancamentos = $lancamentos->get();
    }

    public function hydrate()
    {
        $contaID = $this->Conta->ID;
        $lancamentos = Lancamento::where(function ($query) use ($contaID) {
            return $query->where('ContaDebitoID',$contaID)->orWhere('ContaCreditoID',$contaID);
        })->where('DataContabilidade',date('d/m/Y'));

        if($this->De){
            $de = Carbon::createFromFormat('Y-m-d',$this->De)->format('d/m/Y');
            $lancamentos->where('DataContabilidade','>=',$de);
        }
        if($this->Ate){
            $ate = Carbon::createFromFormat('Y-m-d',$this->Ate)->format('d/m/Y');
            $lancamentos->where('DataContabilidade','<=',$ate);
        }
        $this->Lancamentos = $lancamentos->get();
    }

    public function render()
    {
        return view('livewire.conta.extrato');
    }
}
