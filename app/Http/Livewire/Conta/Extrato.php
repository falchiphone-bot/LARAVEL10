<?php

namespace App\Http\Livewire\Conta;

use App\Models\Conta;
use Livewire\Component;

class Extrato extends Component
{
    public $conta;
    public $lancamentos;

    public function mount($contaID)
    {
        if (!session('hoje')) {
            # code...
        }
        
    }

    public function render()
    {
        return view('livewire.conta.extrato');
    }
}
