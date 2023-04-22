<?php

namespace App\Http\Livewire\Lancamento;

use App\Models\Lancamento;
use Livewire\Component;

class EditarLancamento extends Component
{
    public $lancamento;

    public function mount($lancamento_id = null)
    {
        if ($lancamento_id) {
            $this->lancamento = Lancamento::find($lancamento_id);
        }
    }

    public function render()
    {
        return view('livewire.lancamento.editar-lancamento');
    }
}
