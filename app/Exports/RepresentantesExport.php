<?php

namespace App\Exports;

use App\Models\Representantes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RepresentantesExport implements FromQuery, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $request = new Request($this->filters);

        $query = Representantes::join('Contabilidade.EmpresasUsuarios', 'Representantes.EmpresaID', '=', 'EmpresasUsuarios.EmpresaID')
            ->where('EmpresasUsuarios.UsuarioID', Auth::user()->id)
            ->select('Representantes.*');

        if ($request->filled('nome')) {
            $query->where('Representantes.nome', 'like', '%' . trim($request->input('nome')) . '%');
        }
        if ($request->filled('email')) {
            $query->where('Representantes.email', 'like', '%' . trim($request->input('email')) . '%');
        }

        $mapBool = function ($v) {
            if (is_null($v) || $v === '') return null;
            $v = strtolower((string)$v);
            if (in_array($v, ['1', 'true', 'sim', 'yes'], true)) return 1;
            if (in_array($v, ['0', 'false', 'nao', 'não', 'no'], true)) return 0;
            return null;
        };

        $agente = $mapBool($request->input('agente_fifa'));
        if ($agente !== null) $query->where('agente_fifa', $agente);
        $oficial = $mapBool($request->input('oficial_cbf'));
        if ($oficial !== null) $query->where('oficial_cbf', $oficial);
        $sem = $mapBool($request->input('sem_registro'));
        if ($sem !== null) $query->where('sem_registro', $sem);

        $allowedSorts = ['nome', 'agente_fifa', 'oficial_cbf', 'sem_registro'];
        $sort = $request->input('sort', 'nome');
        if (!in_array($sort, $allowedSorts, true)) $sort = 'nome';
        $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($sort, $dir);
    }

    public function headings(): array
    {
        return ['Nome', 'Telefone', 'Email', 'CPF', 'CNPJ', 'Agente FIFA', 'Oficial CBF', 'Sem registro'];
    }

    public function map($row): array
    {
        return [
            $row->nome,
            $row->telefone,
            $row->email,
            $row->cpf,
            $row->cnpj,
            $row->agente_fifa ? 'SIM' : 'NÃO',
            $row->oficial_cbf ? 'SIM' : 'NÃO',
            $row->sem_registro ? 'SIM' : 'NÃO',
        ];
    }
}
