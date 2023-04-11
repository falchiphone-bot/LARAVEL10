<?php

namespace App\Http\Livewire\Conta;

use App\Models\Conta;
use App\Models\Empresa;
use App\Models\Lancamento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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
    public $exibicao_pesquisa;


    public function mount($contaID)
    {
        $this->De = date('Y-m-d');
        $this->Ate = date('Y-m-d');
        $this->Conta = Conta::find($contaID);

        $lancamentos = Lancamento::where(function ($query) use ($contaID) {
            return $query->where('ContaDebitoID',$contaID)->orWhere('ContaCreditoID',$contaID);
        })->where('DataContabilidade',date('d/m/Y'));

        $this->Lancamentos = $lancamentos->orderBy('DataContabilidade')->get();
    }

    public function updated()
    {
        $contaID = $this->Conta->ID;
        $lancamentos = Lancamento::where(function ($query) use ($contaID) {
            return $query->where('ContaDebitoID',$contaID)->orWhere('ContaCreditoID',$contaID);
        });

        if($this->De){
            $de = Carbon::createFromFormat('Y-m-d',$this->De)->format('d/m/Y');
            $lancamentos->where('DataContabilidade','>=',$de);
        }
        if($this->Ate){
            $ate = Carbon::createFromFormat('Y-m-d',$this->Ate)->format('d/m/Y');
            $lancamentos->where('DataContabilidade','<=',$ate);
        }
        if($this->Descricao){
            $lancamentos->where('Descricao','like',"%$this->Descricao%");
        }
        if($this->Conferido != ""){
            $lancamentos->where('conferido',$this->Conferido);
        }
        if($this->Notificacao != ""){
            $lancamentos->where('notificacao',$this->Notificacao);
        }
        $this->Lancamentos = $lancamentos->orderBy('DataContabilidade')->dd();
    }


    public function render()
    {
        $empresas = Empresa::whereHas('EmpresaUsuario',function ($query){
            return $query->where('UsuarioID',Auth::user()->id);
        })
        ->orderBy('Descricao')->pluck('Descricao','ID');
        return view('livewire.conta.extrato',compact('empresas'));
    }
}
