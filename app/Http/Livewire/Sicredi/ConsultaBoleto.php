<?php

namespace App\Http\Livewire\Sicredi;

use App\Helpers\SicredApiHelper;
use App\Models\ContaCobranca;
use Livewire\Component;

class ConsultaBoleto extends Component
{
    public $resultado;
    public $nosso_numero;
    public $contaCobrancaID;

    public function buscar()
    {
        if($this->contaCobrancaID)
        {
            $conta_cobranca = ContaCobranca::find($this->contaCobrancaID);

            $this->resultado = SicredApiHelper::consultaBoleto($conta_cobranca,$this->nosso_numero);
            if (!$this->resultado['status']) {
                $this->addError('consulta','Nosso Numero não localizado.');
            }
        }
    }

    public function mount()
    {
        $this->resultado = ['status' => false];
    }

    public function render()
    {
        $contasCobrancas = ContaCobranca::pluck('associadobeneficiario', 'id');
        return view('livewire.sicredi.consulta-boleto',compact('contasCobrancas'))->extends('layouts.bootstrap5');
    }
}
