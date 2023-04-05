<?php

namespace App\Http\Livewire\Sicredi;

use App\Helpers\SicredApiHelper;
use App\Models\ContaCobranca;
use Carbon\Carbon;
use Livewire\Component;

class ListarLiquidacao extends Component
{
    public $consulta;
    public $consultaDia;
    public $consultaDiaDisplay;
    public $contaCobranca;

    public function updated()
    {
        $contaCobranca = ContaCobranca::find($this->contaCobranca);
        $consulta = Carbon::createFromFormat('Y-m-d',$this->consultaDia);
        $this->consultaDiaDisplay = $consulta->format('d/m/Y');
        $this->consultaDia = $consulta->format('Y-m-d');
        $this->consulta = SicredApiHelper::boletoLiquidadoDia(
            $contaCobranca->conta,
            $contaCobranca->agencia,
            $contaCobranca->posto,
            $contaCobranca->token_conta,
            $contaCobranca->devSicredi->SICREDI_CLIENT_ID,
            $contaCobranca->devSicredi->SICREDI_CLIENT_SECRET_ID,
            $contaCobranca->devSicredi->SICREDI_TOKEN,
            $consulta->format('d/m/Y')
        );
        // dd($contaCobranca);
    }

    public function mount()
    {
        $contaCobranca = ContaCobranca::first();

        $now = Carbon::now()->subDay(1);
        $this->consultaDiaDisplay = $now->format('d/m/Y');
        $this->consultaDia = $now->format('Y-m-d');
        $this->consulta = SicredApiHelper::boletoLiquidadoDia(
            $contaCobranca->conta,
            $contaCobranca->agencia,
            $contaCobranca->posto,
            $contaCobranca->token_conta,
            $contaCobranca->devSicredi->SICREDI_CLIENT_ID,
            $contaCobranca->devSicredi->SICREDI_CLIENT_SECRET_ID,
            $contaCobranca->devSicredi->SICREDI_TOKEN,
            $now->format('d/m/Y'));
    }

    public function render()
    {
        $contasCobrancas = ContaCobranca::pluck('associadobeneficiario','id');
        return view('livewire.sicredi.listar-liquidacao',compact('contasCobrancas'));
    }
}
