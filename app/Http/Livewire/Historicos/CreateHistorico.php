<?php

namespace App\Http\Livewire\Historicos;

use App\Models\Conta;
use App\Models\Empresa;
use App\Models\Historicos;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Livewire\Component;

class CreateHistorico extends Component
{
    public SupportCollection $empresas;
    public SupportCollection $contas;
    public Historicos $historico;

    public int $contaDebitoID;
    public int $contaCreditoID;
    public int $empresaID;

    public function updatedEmpresaID($value)
    {
        $this->contas = Conta::where('EmpresaID', $value)
            ->where('Grau', 5)
            ->join('Contabilidade.PlanoContas', 'PlanoContas.ID', 'Planocontas_id')
            ->orderBy('PlanoContas.Descricao')
            ->pluck('PlanoContas.Descricao', 'Contas.ID');
    }

    public function mount($historico_id = null)
    {
        $this->contas = new SupportCollection();
        $this->empresas = Empresa::join('Contabilidade.EmpresasUsuarios', 'Empresas.ID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', auth()->user()->id)
            ->OrderBy('Descricao')
            ->pluck('Empresas.Descricao', 'Empresas.ID');

        if ($historico_id) {
            $this->historico = Historicos::find($historico_id);

            $this->contas = Conta::where('EmpresaID', $this->historico->EmpresaID)
                ->where('Grau', 5)
                ->join('Contabilidade.PlanoContas', 'PlanoContas.ID', 'Planocontas_id')
                ->orderBy('PlanoContas.Descricao')
                ->pluck('PlanoContas.Descricao', 'Contas.ID');

            $this->empresaID = $this->historico->EmpresaID;
            $this->contaDebitoID = $this->historico->ContaDebitoID;
            $this->contaCreditoID = $this->historico->ContaCreditoID;
        }

    }

    public function render()
    {
        return view('livewire.historicos.create-historico');
    }
}
