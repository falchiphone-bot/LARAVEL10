<?php

namespace App\Exports;

use App\Models\TipoRepresentante;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TipoRepresentanteExport implements FromQuery, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $request = new Request($this->filters);
        $query = TipoRepresentante::query();
        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%' . trim($request->input('nome')) . '%');
        }
        $allowedSorts = ['nome'];
        $sort = in_array($request->input('sort'), $allowedSorts, true) ? $request->input('sort') : 'nome';
        $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        return $query->orderBy($sort, $dir);
    }

    public function headings(): array
    {
        return ['Nome'];
    }

    public function map($row): array
    {
        return [$row->nome];
    }
}
