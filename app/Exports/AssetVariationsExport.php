<?php
namespace App\Exports;

use App\Models\AssetVariation;
use App\Models\OpenAIChatRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class AssetVariationsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /** @var array<string,mixed> */
    protected array $filters;
    protected string $sort;
    protected ?string $ppartHeaderLabel = null;

    /**
     * @param array<string,mixed> $filters
     */
    public function __construct(array $filters = [], string $sort = 'year_desc')
    {
        $this->filters = $filters;
        $this->sort = $sort ?: 'year_desc';
        // Define rótulo do cabeçalho para "Parcial Mês Ant." (ex.: 16/30)
        try {
            $anchor = Carbon::now();
            $prev = $anchor->copy()->subMonthNoOverflow();
            $day = (int) min((int)$anchor->day, (int)$prev->daysInMonth);
            $last = (int) $prev->daysInMonth;
            $this->ppartHeaderLabel = str_pad((string)$day, 2, '0', STR_PAD_LEFT)
                . '/' . str_pad((string)$last, 2, '0', STR_PAD_LEFT);
        } catch (\Throwable $e) {
            $this->ppartHeaderLabel = null;
        }
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
    $selectedCodes = isset($this->filters['selected_codes']) ? (array)$this->filters['selected_codes'] : [];
    $selectedCodes = array_values(array_filter(array_unique(array_map(function($c){ return strtoupper(trim((string)$c)); }, $selectedCodes)), fn($c)=> $c !== ''));

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

        // Filtrar por lista de códigos selecionados, se fornecida
        if (!empty($selectedCodes)) {
            $q->whereIn(DB::raw('UPPER(asset_code)'), $selectedCodes);
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

        // Precisamos de chat_id para calcular variações parciais por registros
        $rows = $q->get([
            'id', 'asset_code', 'chat_id', 'year', 'month', 'variation', 'created_at', 'updated_at'
        ]);

        // Calcular tendências, mês anterior e parcial mês anterior
        $now = now();
        foreach($rows as $r){
            $firstOf = Carbon::create($r->year, $r->month, 1);
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

            // Mês anterior (%) baseado em year/month anteriores
            $prevYear = ($r->month == 1) ? ($r->year - 1) : $r->year;
            $prevMonth = ($r->month == 1) ? 12 : ($r->month - 1);
            $prevVar = AssetVariation::where('chat_id', $r->chat_id)
                ->where('year', $prevYear)
                ->where('month', $prevMonth)
                ->value('variation');
            $r->setAttribute('export_prev_variation', is_null($prevVar) ? null : (float)$prevVar);

            // Parcial Mês Anterior (%) ancorado em updated_at (fallback created_at)
            $anchor = $r->updated_at ? Carbon::parse($r->updated_at) : ($r->created_at ? Carbon::parse($r->created_at) : Carbon::now());
            $pm = $anchor->copy()->subMonthNoOverflow();
            $pmYear = (int)$pm->year; $pmMonth = (int)$pm->month;
            $startDay = min((int)$anchor->day, (int)$pm->daysInMonth);
            $pStart = Carbon::create($pmYear, $pmMonth, $startDay, 0, 0, 0);
            $pEnd = Carbon::create($pmYear, $pmMonth, (int)$pm->daysInMonth, 23, 59, 59);
            $startPrice = null; $endPrice = null; $ppart = null;
            if($r->chat_id){
                $startPrice = OpenAIChatRecord::where('chat_id', $r->chat_id)
                    ->whereYear('occurred_at', $pmYear)
                    ->whereMonth('occurred_at', $pmMonth)
                    ->where('occurred_at', '>=', $pStart)
                    ->orderBy('occurred_at', 'asc')
                    ->value('amount');
                $endPrice = OpenAIChatRecord::where('chat_id', $r->chat_id)
                    ->whereYear('occurred_at', $pmYear)
                    ->whereMonth('occurred_at', $pmMonth)
                    ->where('occurred_at', '<=', $pEnd)
                    ->orderBy('occurred_at', 'desc')
                    ->value('amount');
            }
            if(is_numeric($startPrice) && is_numeric($endPrice) && (float)$startPrice != 0.0){
                $ppart = (( (float)$endPrice - (float)$startPrice) / (float)$startPrice) * 100.0;
            }
            $r->setAttribute('export_prev_month_partial', is_null($ppart) ? null : (float)$ppart);
            $r->setAttribute('export_prev_month_partial_start', $pStart->toDateString());
            $r->setAttribute('export_prev_month_partial_end', $pEnd->toDateString());
        }

        // Ordenação adicional por Parcial Mês Ant. (%) se solicitado
        if (in_array($this->sort, ['ppart_asc','ppart_desc'])) {
            $rows = $rows->sort(function($a,$b){
                $av = $a->export_prev_month_partial; $bv = $b->export_prev_month_partial;
                $aNull = is_null($av); $bNull = is_null($bv);
                if($aNull && $bNull) return 0;
                if($aNull) return 1; // nulls last
                if($bNull) return -1;
                if($this->sort === 'ppart_asc') return $av <=> $bv;
                return $bv <=> $av; // desc
            })->values();
        }
        return $rows;
    }

    public function headings(): array
    {
        $partialCol = 'Parcial Mês Ant. (%)';
        if ($this->ppartHeaderLabel) {
            $partialCol = 'Parcial Mês Ant. (' . $this->ppartHeaderLabel . ') (%)';
        }
        return [
            'ID', 'Código', 'Ano', 'Mês', 'Variação Atual (%)', 'Mês Anterior (%)', $partialCol, 'Variação Normalizada', 'Confiança', 'Status/Tendência', 'Criado em', 'Atualizado em', 'Parcial Início', 'Parcial Fim'
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
            is_null($row->export_prev_variation ?? null) ? null : (float) $row->export_prev_variation,
            is_null($row->export_prev_month_partial ?? null) ? null : round((float)$row->export_prev_month_partial, 6),
            (float) ($row->export_normalized ?? $row->variation),
            (float) ($row->export_confidence ?? 0),
            (string) ($row->export_trend ?? ''),
            optional($row->created_at)->format('Y-m-d H:i:s'),
            optional($row->updated_at)->format('Y-m-d H:i:s'),
            (string) ($row->export_prev_month_partial_start ?? ''),
            (string) ($row->export_prev_month_partial_end ?? ''),
        ];
    }
}
