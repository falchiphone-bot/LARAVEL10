<?php
namespace App\Exports;

use App\Models\AssetVariation;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AssetVariationsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /** @var array<string,mixed> */
    protected array $filters;
    protected string $sort;

    /**
     * @param array<string,mixed> $filters
     */
    public function __construct(array $filters = [], string $sort = 'year_desc')
    {
        $this->filters = $filters;
        $this->sort = $sort ?: 'year_desc';
    }

    public function collection()
    {
        $q = AssetVariation::query();

        // Filtros (mesma lógica da listagem)
        $year = isset($this->filters['year']) && $this->filters['year'] !== ''
            ? (int) $this->filters['year']
            : 0;
        $month = isset($this->filters['month']) && $this->filters['month'] !== ''
            ? (int) $this->filters['month']
            : 0;
        $code = isset($this->filters['code']) ? trim((string)$this->filters['code']) : '';
        $polarity = $this->filters['polarity'] ?? null; // positive|negative|null

        if ($year) {
            $q->where('year', $year);
        }
        if ($month >= 1 && $month <= 12) {
            $q->where('month', $month);
        }
        if ($code !== '') {
            $q->whereRaw('UPPER(asset_code) = ?', [strtoupper($code)]);
        }
        if ($polarity === 'positive') {
            $q->where('variation', '>', 0);
        } elseif ($polarity === 'negative') {
            $q->where('variation', '<', 0);
        }

        // Ordenação
        switch ($this->sort) {
            case 'variation_asc':
                $q->orderBy('variation', 'asc')->orderBy('year', 'desc')->orderBy('month', 'desc');
                break;
            case 'variation_desc':
                $q->orderBy('variation', 'desc')->orderBy('year', 'desc')->orderBy('month', 'desc');
                break;
            case 'code_asc':
                $q->orderBy('asset_code', 'asc')->orderBy('year', 'desc')->orderBy('month', 'desc');
                break;
            case 'code_desc':
                $q->orderBy('asset_code', 'desc')->orderBy('year', 'desc')->orderBy('month', 'desc');
                break;
            case 'created_asc':
                $q->orderBy('created_at', 'asc');
                break;
            case 'created_desc':
                $q->orderBy('created_at', 'desc');
                break;
            case 'updated_asc':
                $q->orderBy('updated_at', 'asc');
                break;
            case 'updated_desc':
                $q->orderBy('updated_at', 'desc');
                break;
            case 'year_asc':
                $q->orderBy('year', 'asc')->orderBy('month', 'asc');
                break;
            case 'month_asc':
                $q->orderBy('month', 'asc')->orderBy('year', 'desc');
                break;
            case 'month_desc':
                $q->orderBy('month', 'desc')->orderBy('year', 'desc');
                break;
            case 'year_desc':
            default:
                $q->orderBy('year', 'desc')->orderBy('month', 'desc');
        }

        $rows = $q->get([
            'id', 'asset_code', 'year', 'month', 'variation', 'created_at', 'updated_at'
        ]);

        // Calcular tendência / normalização básica para export (assume mês completo para meses passados)
        $now = now();
        foreach($rows as $r){
            $firstOf = \Carbon\Carbon::create($r->year, $r->month, 1);
            $daysMonth = $firstOf->daysInMonth;
            $daysElapsed = ($r->year == $now->year && $r->month == $now->month) ? min($now->day, $daysMonth) : $daysMonth;
            $normalized = $r->variation;
            if($daysElapsed < $daysMonth){
                $normalized = $r->variation * ($daysMonth / max(1,$daysElapsed));
            }
            // Placeholder simples (não temos prev_variation na export): marcar apenas parcial ou completo
            $confidence = $daysMonth > 0 ? min(1.0, $daysElapsed / max(1, ($daysMonth * 0.5))) : 0;
            $r->setAttribute('export_normalized', round($normalized, 6));
            $r->setAttribute('export_confidence', round($confidence, 4));
            $r->setAttribute('export_trend', $daysElapsed < $daysMonth ? 'PARCIAL' : 'COMPLETO');
        }
        return $rows;
    }

    public function headings(): array
    {
        return [
            'ID', 'Código', 'Ano', 'Mês', 'Variação', 'Variação Normalizada', 'Confiança', 'Status/Tendência', 'Criado em', 'Atualizado em'
        ];
    }

    /**
     * @param \App\Models\AssetVariation $row
     */
    public function map($row): array
    {
        return [
            $row->id,
            $row->asset_code,
            $row->year,
            (int) $row->month,
            (float) $row->variation,
            (float) ($row->export_normalized ?? $row->variation),
            (float) ($row->export_confidence ?? 0),
            (string) ($row->export_trend ?? ''),
            optional($row->created_at)->format('Y-m-d H:i:s'),
            optional($row->updated_at)->format('Y-m-d H:i:s'),
        ];
    }
}
