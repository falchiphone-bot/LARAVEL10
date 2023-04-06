<?php

namespace App\Http\Livewire\ContaCobranca;

use App\Models\DevSicredi;
use App\Models\Historicos;
use Livewire\Component;

class ContaCobrancaCampos extends Component
{
    public $contaCobranca;
    public $historicoCredito;

    public function mount($contaCobranca)
    {
        $this->contaCobranca = $contaCobranca;
    }

    public function render()
    {
        $contasDev = DevSicredi::orderBy('DESENVOLVEDOR')->pluck('DESENVOLVEDOR','id');
        $historicos = Historicos::orderBy('Descricao')->pluck('Descricao','ID');
        return view('livewire.conta-cobranca.conta-cobranca-campos',compact('contasDev','historicos'));
    }
}
