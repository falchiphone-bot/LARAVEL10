<?php

namespace App\Exports;

use App\Models\Representantes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $empresaIds = DB::table('Contabilidade.EmpresasUsuarios')
            ->where('UsuarioID', Auth::id())
            ->pluck('EmpresaID');

        $query = Representantes::query()
            ->whereIn('EmpresaID', $empresaIds)
            ->with('MostraEmpresa')
            ->select('representantes.*');
        // Filtro por empresa específica
        if ($request->filled('empresa_id')) {
            $empresaIdReq = (int)$request->input('empresa_id');
            if ($empresaIds->contains($empresaIdReq)) {
                $query->where('EmpresaID', $empresaIdReq);
            }
        }

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
    return ['Empresa', 'Nome', 'Telefone', 'Email', 'CPF', 'CNPJ', 'Agente FIFA', 'Oficial CBF', 'Sem registro'];
    }

    public function map($row): array
    {
        return [
            optional($row->MostraEmpresa)->Descricao,
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
