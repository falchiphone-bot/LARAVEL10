<?php

namespace App\Exports;

use App\Models\TipoArquivo;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TipoArquivoExport implements FromQuery, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = TipoArquivo::query();
        if (!empty($this->filters['nome'])) {
            $query->where('nome', 'like', '%' . trim($this->filters['nome']) . '%');
        }
        $allowedSorts = ['nome'];
        $sort = $this->filters['sort'] ?? 'nome';
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($this->filters['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        return $query->orderBy($sort, $dir);
    }

    public function headings(): array
    {
        return ['Nome'];
    }

    public function map($row): array
    {
        return [
            $row->nome,
        ];
    }
}
