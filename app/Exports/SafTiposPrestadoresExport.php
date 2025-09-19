<?php

namespace App\Exports;

use App\Models\SafTipoPrestador;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SafTiposPrestadoresExport implements FromQuery, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = SafTipoPrestador::query()->with('funcaoProfissional');

        $q = trim((string)($this->filters['q'] ?? ''));
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $w->where('nome', 'like', "%{$q}%")
                  ->orWhere('cidade', 'like', "%{$q}%")
                  ->orWhere('uf', 'like', "%{$q}%")
                  ->orWhere('pais', 'like', "%{$q}%");
            });
        }
        $funcaoId = $this->filters['funcao_profissional_id'] ?? null;
        if (!empty($funcaoId)) {
            $query->where('funcao_profissional_id', $funcaoId);
        }

        $allowedSorts = ['nome','cidade','uf','pais','funcao'];
        $sort = $this->filters['sort'] ?? 'nome';
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($this->filters['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        if ($sort === 'funcao') {
            $query->leftJoin('FuncaoProfissional as fp', 'fp.id', '=', 'saf_tipos_prestadores.funcao_profissional_id')
                  ->select('saf_tipos_prestadores.*')
                  ->orderBy('fp.nome', $dir);
        } else {
            $query->orderBy($sort, $dir);
        }

        return $query;
    }

    public function headings(): array
    {
        return ['Nome','Função Profissional','Cidade','UF','País'];
    }

    public function map($row): array
    {
        // Como withMapping recebe o modelo, usamos a relação carregada
        return [
            $row->nome,
            optional($row->funcaoProfissional)->nome,
            $row->cidade,
            $row->uf,
            $row->pais,
        ];
    }
}
