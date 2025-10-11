<?php
namespace App\Exports;

use App\Models\AssetVariation;
use App\Models\OpenAIChatRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
// Cabeçalho multi-linha dispensa eventos e custom start cell

/**
 * Exporta a alocação com as mesmas colunas da view (uma linha por ativo selecionado).
 */
class AssetAllocationsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /** @var array<string,mixed> */
    protected array $filters;

    /**
     * @param array<string,mixed> $filters
     */
    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
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

        // Parâmetros de alocação
        $capital = $this->parseMoneyLike($this->filters['capital'] ?? null);
        $capPct = $this->parseNumberLike($this->filters['cap_pct'] ?? '35');
        $targetPct = $this->parseNumberLike($this->filters['target_pct'] ?? '20');
        $currency = strtoupper((string)($this->filters['currency'] ?? 'USD'));
        // Conversão BRL/USD (display) — no controller a taxa vem de MoedasValores; aqui tentamos recuperar via DB se necessário
        $rate = null;
        try {
            if ($currency === 'BRL') {
                // idmoeda=1 costuma ser USD->BRL neste projeto
                $rv = DB::table('moedas_valores')->where('idmoeda', 1)->orderBy('data', 'desc')->value('valor');
                if (is_numeric($rv)) { $rate = (float)$rv; }
            }
        } catch (\Throwable $e) { /* noop */ }
        $dispMul = ($currency === 'BRL' && $rate) ? $rate : 1.0;
        $curSymbol = ($currency === 'BRL' && $rate) ? 'R$' : '$US';
        $calcCapital = $capital;
        if ($capital !== null && $currency === 'BRL' && $rate) {
            // Capital informado em BRL: converter para USD para os cálculos
            $calcCapital = $capital / $rate;
        }
        $cap = ($capPct !== null) ? max(0.0, min(1.0, ((float)$capPct)/100.0)) : 0.35;
        $target = ($targetPct !== null) ? max(-1.0, min(10.0, ((float)$targetPct)/100.0)) : 0.20;

        // Dataset base: aplicar filtros e restringir aos selected_codes
        $q = AssetVariation::with('chat');
        if ($year) { $q->where('year', $year); }
        if ($month >= 1 && $month <= 12) { $q->where('month', $month); }
        if ($code !== '') { $q->whereRaw('UPPER(asset_code) = ?', [strtoupper($code)]); }
        if ($polarity === 'positive') { $q->where('variation', '>', 0); }
        elseif ($polarity === 'negative') { $q->where('variation', '<', 0); }
        if (!empty($selectedCodes)) {
            $q->whereIn(DB::raw('UPPER(asset_code)'), $selectedCodes);
        }

        // Subquery para variação anterior (mesmo chat, mês anterior)
        $driver = DB::getDriverName();
        if ($driver === 'sqlsrv') {
            $subSql = "select top 1 av2.variation from asset_variations av2\n                where av2.chat_id = asset_variations.chat_id\n                  and ( (asset_variations.month > 1 AND av2.year = asset_variations.year AND av2.month = asset_variations.month - 1)\n                        OR (asset_variations.month = 1 AND av2.year = asset_variations.year - 1 AND av2.month = 12) )";
        } else {
            $subSql = "select av2.variation from asset_variations av2\n                where av2.chat_id = asset_variations.chat_id\n                  and ( (asset_variations.month > 1 AND av2.year = asset_variations.year AND av2.month = asset_variations.month - 1)\n                        OR (asset_variations.month = 1 AND av2.year = asset_variations.year - 1 AND av2.month = 12) )\n                limit 1";
        }
        $q->select('asset_variations.*');
        $q->selectSub($subSql, 'prev_variation');
        // Ordenar de forma determinística (mais recentes primeiro)
        $q->orderBy('year', 'desc')->orderBy('month', 'desc');

        $rows = $q->get();
        // Escolher 1 linha por código (mais recente)
        $byCode = [];
        foreach ($rows as $r) {
            $codeKey = strtoupper(trim((string)$r->asset_code));
            if ($codeKey === '') { continue; }
            if (!isset($byCode[$codeKey])) { $byCode[$codeKey] = $r; }
        }

        // Montar linhas auxiliares (como $rows na blade)
        $aux = [];
        $now = Carbon::now();
        foreach ($byCode as $r) {
            $codeKey = strtoupper(trim((string)$r->asset_code));
            $pv = $r->prev_variation !== null ? (float)$r->prev_variation : null;
            $cur = (float)$r->variation;
            $diff = (!is_null($pv)) ? ($cur - $pv) : null;
            // Tendência (mesma função do controller)
            $firstOf = Carbon::create($r->year, $r->month, 1);
            $daysMonth = $firstOf->daysInMonth;
            $daysElapsed = ($r->year == $now->year && $r->month == $now->month)
                ? min($now->day, $daysMonth)
                : $daysMonth;
            $trend = $this->classifyTrend($pv, $cur, $daysElapsed, $daysMonth);
            $aux[] = [
                'code' => $codeKey,
                'title' => $r->chat?->title ?? '',
                'year' => (int)$r->year,
                'month'=> (int)$r->month,
                'cur' => $cur,
                'prev' => $pv,
                'diff' => $diff,
                'chat_id' => $r->chat_id,
                'trend_code' => $trend['code'] ?? null,
                'trend_label'=> $trend['label'] ?? null,
                'trend_badge'=> $trend['badge'] ?? 'secondary',
            ];
        }

        // Seleção já aplicada pelo whereIn; apenas garantir ordem estável por código
        usort($aux, fn($a,$b)=> strcmp($a['code'], $b['code']));

        // Scoring: quando em modo seleção, usar diff>0, senão cur>0, senão 0 (igual à blade)
        $accel = $aux; // dataset de trabalho
        $sum = 0.0;
        foreach ($accel as &$r) {
            $d = $r['diff']; $c = $r['cur'];
            $score = 0.0;
            if(!is_null($d) && $d > 0) { $score = (float)$d; }
            elseif(!is_null($c) && $c > 0) { $score = (float)$c; }
            $r['_score'] = $score;
            $sum += $score;
        }
        unset($r);
        if ($sum <= 0 && count($accel) > 0) {
            foreach ($accel as &$r) { $r['_score'] = 1.0; }
            unset($r); $sum = count($accel);
        }

        // Weights base e cap via water-filling
        $baseW = [];
        foreach ($accel as $r) { $baseW[] = ($sum > 0) ? (($r['_score'] ?? 0)/$sum) : (1.0/max(count($accel),1)); }
        $n = count($accel);
        $finalW = array_fill(0, $n, 0.0);
        $unc = range(0, $n-1);
        $remaining = 1.0;
        for ($iter=0; $iter<10 && $remaining>1e-9 && count($unc)>0; $iter++) {
            $sumBase = 0.0; foreach ($unc as $j) { $sumBase += $baseW[$j]; }
            if ($sumBase <= 0) {
                $eq = $remaining / max(count($unc),1);
                foreach ($unc as $j) { $finalW[$j] += $eq; }
                $remaining = 0.0; break;
            }
            $toCap = [];
            foreach ($unc as $j) {
                $w = $remaining * ($baseW[$j]/max($sumBase,1e-12));
                if ($w > $cap + 1e-9) { $toCap[] = $j; }
            }
            if (count($toCap) === 0) {
                foreach ($unc as $j) { $finalW[$j] += $remaining * ($baseW[$j]/$sumBase); }
                $remaining = 0.0; break;
            }
            foreach ($toCap as $j) {
                $finalW[$j] += $cap;
                $remaining -= $cap;
                $unc = array_values(array_filter($unc, fn($x)=> $x !== $j));
            }
        }
        if ($remaining > 1e-9) {
            $best = -1; $bestVal = -1.0;
            for ($i=0;$i<$n;$i++) { if ($finalW[$i] < $cap - 1e-9 && $finalW[$i] > $bestVal) { $bestVal = $finalW[$i]; $best = $i; } }
            if ($best >= 0) { $finalW[$best] += $remaining; $remaining = 0.0; }
        }

        // Montar alocação com amount/target/last_price/qty (uma linha por ativo)
        $alloc = [];
        $seenCodes = [];
        foreach ($accel as $idx => $r) {
            $codeKey = strtoupper($r['code'] ?? '');
            if ($codeKey !== '' && isset($seenCodes[$codeKey])) { continue; }
            $w = max(0.0, min(1.0, $finalW[$idx]));
            $valUsd = $w * ($calcCapital ?? 0.0);
            $lastPrice = null;
            $cid = $r['chat_id'] ?? null;
            if ($cid) {
                try {
                    $lp = OpenAIChatRecord::where('chat_id', $cid)->orderByDesc('occurred_at')->value('amount');
                    if (is_numeric($lp)) { $lastPrice = (float)$lp; }
                } catch (\Throwable $e) { /* noop */ }
            }
            $qty = ($lastPrice && $lastPrice > 0) ? ($valUsd / $lastPrice) : null;
            $alloc[] = [
                'code' => $codeKey,
                'title' => $r['title'] ?? '',
                'cur' => $r['cur'],
                'prev' => $r['prev'],
                'diff' => $r['diff'],
                'trend_label' => $r['trend_label'] ?? null,
                'trend_badge' => $r['trend_badge'] ?? 'secondary',
                'weight' => $w,
                // valores para exibição conforme moeda escolhida
                'amount_disp' => $valUsd * $dispMul,
                'gain_target_disp' => ($valUsd * $target) * $dispMul,
                'last_price_disp' => ($lastPrice !== null) ? ($lastPrice * $dispMul) : null,
                'qty' => $qty,
                'curSymbol' => $curSymbol,
                // Aloc.Fato: buscar do cache (valor digitado na UI), sem conversão extra — assume-se na moeda exibida
                'alloc_fato_disp' => (function() use($r){
                    $code = strtoupper($r['code'] ?? '');
                    // Preferir ano/mês da própria linha; se ausentes, cair para filtros globais
                    $y = (int)($r['year'] ?? 0);
                    $m = (int)($r['month'] ?? 0);
                    if($y<=0) { $y = isset($this->filters['year']) && $this->filters['year'] !== '' ? (int)$this->filters['year'] : 0; }
                    if($m<=0) { $m = isset($this->filters['month']) && $this->filters['month'] !== '' ? (int)$this->filters['month'] : 0; }
                    if($y<=0 || $m<=0 || $code==='') return null;
                    $key = 'openai:variations:alloc_fato:'.$y.':'.$m.':'.$code;
                    $v = Cache::get($key);
                    return is_numeric($v) ? (float)$v : null;
                })(),
            ];
            if ($codeKey !== '') { $seenCodes[$codeKey] = true; }
        }

        // Para CSV: inserir linha inicial com mensagem antes do cabeçalho.
        // Para XLSX, também manteremos (a planilha terá a linha 1 com mensagem e cabeçalho a partir da linha 2 via startCell()).
            return new Collection($alloc);
    }

    public function headings(): array
    {
        $curSymbol = strtoupper((string)($this->filters['currency'] ?? 'USD')) === 'BRL' ? 'R$' : '$US';
        // Cabeçalho multi-linha: primeira linha com a mensagem, segunda linha com os títulos das colunas
        return [
            ['Exportado dos registros selecionados CSV/XLSX'],
            [
                'Código',
                'Conversa/Ativo',
                'Variação Atual (%)',
                'Anterior (%)',
                'Diferença (pp)',
                'Tendência',
                'Aloc.Fato ('.$curSymbol.')',
                'Peso (%)',
                'Valor ('.$curSymbol.')',
                'Ganho alvo ('.$curSymbol.')',
                'Preço atual ('.$curSymbol.')',
                'Qtd',
            ],
        ];
    }

    /**
     * @param array<string,mixed> $row
     */
    public function map($row): array
    {
        return [
            $row['code'],
            $row['title'],
            $this->fmt4($row['cur']),
            $this->fmt4($row['prev']),
            $this->fmt4($row['diff']),
            (string)($row['trend_label'] ?? ''),
            $this->fmt2($row['alloc_fato_disp'] ?? null),
            $this->fmt2(isset($row['weight']) ? ($row['weight']*100) : null),
            $this->fmt2($row['amount_disp'] ?? null),
            $this->fmt2($row['gain_target_disp'] ?? null),
            $this->fmt2($row['last_price_disp'] ?? null),
            $this->fmtQty($row['qty'] ?? null),
        ];
    }

    // Sem eventos/startCell: o cabeçalho multi-linha já coloca a mensagem na primeira linha.

    // Auxiliares
    protected function parseMoneyLike($v): ?float
    {
        if ($v === null || $v === '') { return null; }
        $s = (string)$v;
        // aceita formatos tipo 150.000,00 ou 150,000.00
        $s = preg_replace('/[^\d,.-]/', '', $s);
        if ($s === null) { return null; }
        // Se contém vírgula e ponto, assumir BR e trocar
        if (strpos($s, ',') !== false && strpos($s, '.') !== false) {
            // remover milhares e trocar decimal
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } else {
            // Se contém vírgula mas não ponto, trocar vírgula por ponto
            if (strpos($s, ',') !== false && strpos($s, '.') === false) {
                $s = str_replace(',', '.', $s);
            }
        }
        return is_numeric($s) ? (float)$s : null;
    }

    protected function parseNumberLike($v): ?float
    {
        if ($v === null || $v === '') { return null; }
        $s = (string)$v;
        $s = preg_replace('/[^\d,.-]/', '', $s);
        if ($s === null) { return null; }
        if (strpos($s, ',') !== false && strpos($s, '.') !== false) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } else {
            if (strpos($s, ',') !== false && strpos($s, '.') === false) {
                $s = str_replace(',', '.', $s);
            }
        }
        return is_numeric($s) ? (float)$s : null;
    }

    /**
     * Classificação de tendência (mesma regra da controller)
     */
    protected function classifyTrend(?float $p, ?float $c, int $daysElapsed, int $daysMonth): array
    {
        if ($p === null || $c === null) {
            return ['code'=>'sem_historico','label'=>'Sem histórico','badge'=>'secondary','confidence'=>0,'normalized'=>$c];
        }
        $minDelta = 0.2;
        $confidence = 0.0;
        if ($daysMonth > 0) {
            $confidence = min(1.0, $daysElapsed / max(1, ($daysMonth * 0.5)));
        }
        $normalized = $c;
        if ($daysElapsed < $daysMonth) {
            $factor = $daysMonth / max(1,$daysElapsed);
            $normalized = $c * $factor;
        }
        $pVal = $p; $cNorm = $normalized;
        if($pVal < 0 && $cNorm > 0){ return ['code'=>'reversao_alta','label'=>'Reversão Alta','badge'=>'success','confidence'=>$confidence,'normalized'=>$normalized]; }
        if($pVal > 0 && $cNorm < 0){ return ['code'=>'reversao_baixa','label'=>'Reversão Baixa','badge'=>'danger','confidence'=>$confidence,'normalized'=>$normalized]; }
        if($pVal >= 0 && $cNorm >= 0){
            if($cNorm >= $pVal + $minDelta) return ['code'=>'alta_acelerando','label'=>'Alta Acelerando','badge'=>'success','confidence'=>$confidence,'normalized'=>$normalized];
            if($cNorm <= $pVal - $minDelta) return ['code'=>'alta_perdendo','label'=>'Alta Perdendo Força','badge'=>'warning','confidence'=>$confidence,'normalized'=>$normalized];
            return ['code'=>'alta_estavel','label'=>'Alta Estável','badge'=>'success','confidence'=>$confidence,'normalized'=>$normalized];
        }
        if($pVal <= 0 && $cNorm <= 0){
            if($cNorm <= $pVal - $minDelta) return ['code'=>'queda_acelerando','label'=>'Queda Acelerando','badge'=>'danger','confidence'=>$confidence,'normalized'=>$normalized];
            if($cNorm >= $pVal + $minDelta) return ['code'=>'queda_aliviando','label'=>'Queda Aliviando','badge'=>'info','confidence'=>$confidence,'normalized'=>$normalized];
            return ['code'=>'queda_estavel','label'=>'Queda Estável','badge'=>'danger','confidence'=>$confidence,'normalized'=>$normalized];
        }
        return ['code'=>'neutro','label'=>'Neutro','badge'=>'secondary','confidence'=>$confidence,'normalized'=>$normalized];
    }

    protected function fmt2($v): string
    {
        if ($v === null || !is_numeric($v)) { return ''; }
        return number_format((float)$v, 2, ',', '.');
    }

    protected function fmt4($v): string
    {
        if ($v === null || !is_numeric($v)) { return ''; }
        return number_format((float)$v, 4, ',', '.');
    }

    protected function fmtQty($v): string
    {
        if ($v === null || !is_numeric($v)) { return ''; }
        return number_format((float)$v, 4, ',', '.');
    }
}
