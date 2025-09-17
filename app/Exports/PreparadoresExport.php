<?php

namespace App\Exports;

use App\Models\Preparadores;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PreparadoresExport implements FromQuery, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Preparadores::query();

        if (!empty($this->filters['nome'])) {
            $query->where('nome', 'like', '%' . trim($this->filters['nome']) . '%');
        }
        if (!empty($this->filters['email'])) {
            $query->where('email', 'like', '%' . trim($this->filters['email']) . '%');
        }
        if (!empty($this->filters['telefone'])) {
            $query->where('telefone', 'like', '%' . trim($this->filters['telefone']) . '%');
        }
        if (!empty($this->filters['licencaCBF'])) {
            $query->where('licencaCBF', 'like', '%' . trim($this->filters['licencaCBF']) . '%');
        }

        $allowedSorts = ['nome','email','telefone','licencaCBF'];
        $sort = $this->filters['sort'] ?? 'nome';
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'nome'; }
        $dir = strtolower($this->filters['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($sort, $dir);
    }

    public function headings(): array
    {
        return ['Nome','Email','Telefone','Licença CBF'];
    }

    public function map($row): array
    {
        return [
            $row->nome,
            $row->email,
            $row->telefone,
            $row->licencaCBF,
        ];
    }
}
