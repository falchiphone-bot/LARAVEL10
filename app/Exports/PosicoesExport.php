<?php

namespace App\Exports;

use App\Models\Posicoes;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PosicoesExport implements FromQuery, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Posicoes::query()->with('MostraTipoEsporte');
        if (!empty($this->filters['nome'])) {
            $query->where('nome', 'like', '%' . trim($this->filters['nome']) . '%');
        }
        if (!empty($this->filters['tipo_esporte'])) {
            $query->where('tipo_esporte', (int)$this->filters['tipo_esporte']);
        }
        $allowedSorts = ['nome'];
        $sort = $this->filters['sort'] ?? 'nome';
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($this->filters['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        return $query->orderBy($sort, $dir);
    }

    public function headings(): array
    {
        return ['Nome','Esporte'];
    }

    public function map($row): array
    {
        return [
            $row->nome,
            optional($row->MostraTipoEsporte)->nome,
        ];
    }
}
