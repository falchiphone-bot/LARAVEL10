<?php

namespace App\Http\Livewire\Lancamento;

use App\Models\Conta;
use App\Models\Empresa;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TrocaEmpresa extends Component
{
    public $empresas;
    public $novaempresa;
    public $novacontadebito;
    public $novacontacredito;
    public $contasnovas = [];

    public $lancamento_id;

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function empresaSelecionada()
    {
        $this->contasnovas = Conta::where('EmpresaID',$this->novaempresa)->where('Grau',5)
        ->join('Contabilidade.PlanoContas','PlanoContas.ID','Planocontas_id')
        ->orderBy('PlanoContas.Descricao')
        ->pluck('PlanoContas.Descricao', 'Contas.ID');
        $this->emit('refreshComponent');
    }
    public function hydrate()
    {
        $this->emit('select2');
    }

    public function mount($lancamento_id)
    {
        $this->lancamento_id = $lancamento_id;
        $this->empresas = Empresa::join('Contabilidade.EmpresasUsuarios','EmpresasUsuarios.EmpresaID','Empresas.ID')
        ->where('EmpresasUsuarios.UsuarioID',Auth::user()->id)->orderBy('Descricao')->pluck('Descricao','Empresas.ID');
    }
    public function render()
    {
        return view('livewire.lancamento.troca-empresa');
    }
}
