<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

/**
 * Exporta a visualização de despesas (cache) incluindo metadados de classificação.
 * Mantém colunas originais da planilha + campos auxiliares para futura importação.
 */
class PreviewDespesasExport implements FromCollection, WithHeadings
{
    use Exportable;

    protected Collection $rows;
    protected array $headings;

    /**
     * @param array $rows Linhas já formatadas (associativas) em ordem.
     * @param array $headings Ordem das colunas.
     */
    public function __construct(array $rows, array $headings)
    {
        $this->rows = new Collection($rows);
        $this->headings = $headings;
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
