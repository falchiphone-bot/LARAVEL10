<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromQuery, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = User::query();

        $q = trim((string)($this->filters['q'] ?? ''));
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $createdFrom = $this->filters['created_from'] ?? null;
        $createdTo = $this->filters['created_to'] ?? null;
        if (!empty($createdFrom)) { $query->whereDate('created_at', '>=', $createdFrom); }
        if (!empty($createdTo)) { $query->whereDate('created_at', '<=', $createdTo); }

        $allowedSorts = ['name','email','created_at'];
        $sort = $this->filters['sort'] ?? 'name';
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'name'; }
        $dir = strtolower($this->filters['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sort, $dir);
        if ($sort !== 'name') {
            $query->orderBy('name', 'asc');
        }

        return $query;
    }

    public function headings(): array
    {
        return ['Nome', 'Email', 'Data de cadastro'];
    }

    public function map($row): array
    {
        return [
            $row->name,
            $row->email,
            optional($row->created_at)->format('d/m/Y H:i'),
        ];
    }
}
