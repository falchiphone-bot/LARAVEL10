<?php
namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;

class LancamentoExport implements FromCollection
{
    use Exportable;

    protected $lancamento;

    public function __construct(Collection $lancamento)
    {
        $this->lancamento = $lancamento;
    }

    public function collection()
    {
        return $this->lancamento;
    }
}

