<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetVariation;
use App\Models\OpenAIChat;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AssetVariationsExport;
use App\Exports\AssetAllocationsExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use App\Models\UserHolding;
use App\Models\MoedasValores;
use Carbon\Carbon;
use App\Models\OpenAIChatRecord;
use Illuminate\Support\Facades\Cache;

class AssetVariationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:OPENAI - CHAT'])->only('index','store','exportCsv','exportXlsx','batchFlags','importSelected','clearCache','saveAllocFato');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', OpenAIChat::class);
    $noPage = $request->boolean('no_page');
    $yearInput = $request->input('year');
    $year = is_numeric($yearInput) ? (int)$yearInput : (int)date('Y');
    $monthInput = $request->input('month');
    $month = is_numeric($monthInput) ? (int)$monthInput : 0; // 1..12
        $code = trim($request->input('code',''));
        $polarity = $request->input('polarity'); // positive | negative
    $change = $request->input('change'); // melhoria|piora|igual|null
    $trendFilter = $request->input('trend'); // códigos de tendência
        // Dia de âncora para Parcial do Mês Anterior (1..31); opcional
        $ppartDayInput = $request->input('ppart_day');
        $ppartDay = null;
        if (is_numeric($ppartDayInput)) {
            $ppartDay = max(1, min(31, (int)$ppartDayInput));
        }
        // Dia de término opcional para Parcial (1..31); se informado, o período vai até este dia
        $ppartEndInput = $request->input('ppart_end_day');
        $ppartEndDay = null;
        if (is_numeric($ppartEndInput)) {
            $ppartEndDay = max(1, min(31, (int)$ppartEndInput));
        }
        $grouped = (bool)$request->boolean('grouped');
        $sparkWindow = (int)($request->input('spark_window') ?: 6);
        if($sparkWindow < 3) $sparkWindow = 3; if($sparkWindow > 24) $sparkWindow = 24;
    $sort = $request->input('sort', 'year_desc'); // adiciona diff_asc|diff_desc|prev_asc|prev_desc
    // Quando "Sem paginação" (no_page) estiver habilitado, obedecer o filtro de mês normalmente;
    // e se o ano não foi explicitamente informado, não aplicar o ano padrão no SQL
    $applyYear = is_numeric($yearInput) ? (int)$yearInput : ($noPage ? 0 : (int)date('Y'));
    $applyMonth = $month;

        // Função auxiliar de classificação de tendência (compartilhada)
        $classifyTrend = function(?float $p, ?float $c, int $daysElapsed, int $daysMonth) {
            if ($p === null || $c === null) {
                return ['code'=>'sem_historico','label'=>'Sem histórico','badge'=>'secondary','confidence'=>0,'normalized'=>$c];
            }
            $minDelta = 0.2; // diferença mínima em pontos percentuais
            $confidence = 0.0;
            if ($daysMonth > 0) {
                $confidence = min(1.0, $daysElapsed / max(1, ($daysMonth * 0.5))); // meia janela => confiança ~1
            }
            $normalized = $c;
            if ($daysElapsed < $daysMonth) {
                $factor = $daysMonth / max(1,$daysElapsed);
                $normalized = $c * $factor;
            }
            $pVal = $p; $cNorm = $normalized;
            // Reversões
            if($pVal < 0 && $cNorm > 0){ return ['code'=>'reversao_alta','label'=>'Reversão Alta','badge'=>'success','confidence'=>$confidence,'normalized'=>$normalized]; }
            if($pVal > 0 && $cNorm < 0){ return ['code'=>'reversao_baixa','label'=>'Reversão Baixa','badge'=>'danger','confidence'=>$confidence,'normalized'=>$normalized]; }
            // Ambos positivos
            if($pVal >= 0 && $cNorm >= 0){
                if($cNorm >= $pVal + $minDelta) return ['code'=>'alta_acelerando','label'=>'Alta Acelerando','badge'=>'success','confidence'=>$confidence,'normalized'=>$normalized];
                if($cNorm <= $pVal - $minDelta) return ['code'=>'alta_perdendo','label'=>'Alta Perdendo Força','badge'=>'warning','confidence'=>$confidence,'normalized'=>$normalized];
                return ['code'=>'alta_estavel','label'=>'Alta Estável','badge'=>'success','confidence'=>$confidence,'normalized'=>$normalized];
            }
            // Ambos negativos
            if($pVal <= 0 && $cNorm <= 0){
                if($cNorm <= $pVal - $minDelta) return ['code'=>'queda_acelerando','label'=>'Queda Acelerando','badge'=>'danger','confidence'=>$confidence,'normalized'=>$normalized];
                if($cNorm >= $pVal + $minDelta) return ['code'=>'queda_aliviando','label'=>'Queda Aliviando','badge'=>'info','confidence'=>$confidence,'normalized'=>$normalized];
                return ['code'=>'queda_estavel','label'=>'Queda Estável','badge'=>'danger','confidence'=>$confidence,'normalized'=>$normalized];
            }
            return ['code'=>'neutro','label'=>'Neutro','badge'=>'secondary','confidence'=>$confidence,'normalized'=>$normalized];
        };

        // Se modo agrupado: construímos dataset agregado e retornamos sem paginação convencional.
        if ($grouped) {
            $base = AssetVariation::query();
            if($code !== ''){ $base->whereRaw('UPPER(asset_code) = ?', [strtoupper($code)]); }
            if ($polarity === 'positive') { $base->where('variation', '>', 0); }
            elseif ($polarity === 'negative') { $base->where('variation', '<', 0); }
            // Limitamos número de chats para evitar carga alta; pode ser ajustado futuramente.
            $chatIds = $base->select('chat_id')
                ->whereNotNull('chat_id')
                ->distinct()
                ->limit(300)
                ->pluck('chat_id');
            $seriesRows = AssetVariation::with('chat')
                ->whereIn('chat_id', $chatIds)
                ->orderBy('year','desc')->orderBy('month','desc')
                ->get();
            // Agrupar por chat_id
            $groupedData = [];
            foreach($seriesRows as $row){
                $cid = $row->chat_id;
                if(!isset($groupedData[$cid])){
                    $groupedData[$cid] = [
                        'chat_id' => $cid,
                        'asset_code' => $row->asset_code,
                        'chat_title' => optional($row->chat)->title,
                        'rows' => [],
                    ];
                }
                if (count($groupedData[$cid]['rows']) < $sparkWindow) {
                    $groupedData[$cid]['rows'][] = $row; // armazenar em ordem desc por enquanto
                }
            }
            // Processar cada grupo: ordenar, calcular var anterior, diff e tendência
            foreach($groupedData as &$g){
                usort($g['rows'], function($a,$b){
                    if($a->year === $b->year) return $a->month <=> $b->month;
                    return $a->year < $b->year ? -1 : 1;
                });
                $latest = end($g['rows']);
                $prevYear = $latest->month == 1 ? ($latest->year - 1) : $latest->year;
                $prevMonth = $latest->month == 1 ? 12 : ($latest->month - 1);
                $prev = null;
                foreach($g['rows'] as $rPrev){
                    if(!is_object($rPrev)) { continue; }
                    if($rPrev->year == $prevYear && $rPrev->month == $prevMonth){
                        $prev = $rPrev->variation; break;
                    }
                }
                $g['latest'] = $latest;
                $g['prev_variation'] = $prev;
                $g['diff'] = (!is_null($prev)) ? ($latest->variation - $prev) : null;
                // Tendência (considera mês corrente parcial)
                $firstOf = Carbon::create($latest->year, $latest->month, 1);
                $daysMonth = $firstOf->daysInMonth;
                $now = Carbon::now();
                $daysElapsed = ($latest->year == $now->year && $latest->month == $now->month) ? min($now->day, $daysMonth) : $daysMonth;
                $trend = $classifyTrend($prev, $latest->variation, $daysElapsed, $daysMonth);
                $g['trend_code'] = $trend['code'];
                $g['trend_label'] = $trend['label'];
                $g['trend_badge'] = $trend['badge'];
                $g['trend_confidence'] = $trend['confidence'];
                $g['normalized_variation'] = $trend['normalized'];
                $g['days_elapsed'] = $daysElapsed;
                $g['days_month'] = $daysMonth;
                // Filtragem por change no modo agrupado (baseada em diff)
                $ok = true;
                if($change === 'melhoria'){ $ok = !is_null($g['diff']) && $g['diff'] > 0; }
                elseif($change === 'piora'){ $ok = !is_null($g['diff']) && $g['diff'] < 0; }
                elseif($change === 'igual'){ $ok = !is_null($g['diff']) && abs($g['diff']) < 1e-12; }
                if(!$ok){ $g['__discard'] = true; }
            }
            unset($g);
            $groupedData = array_filter($groupedData, fn($g)=>empty($g['__discard']));
            // Ordenação básica em agrupado: por código asc; se sort diff_asc/diff_desc aplicar.
            if($sort === 'diff_asc'){
                usort($groupedData, fn($a,$b)=> ($a['diff'] <=> $b['diff']));
            } elseif($sort === 'diff_desc'){
                usort($groupedData, fn($a,$b)=> ($b['diff'] <=> $a['diff']));
            } elseif($sort === 'prev_asc') {
                usort($groupedData, function($a,$b){
                    $av = $a['prev_variation']; $bv = $b['prev_variation'];
                    if($av === null && $bv === null) return 0;
                    if($av === null) return 1; // nulls last
                    if($bv === null) return -1;
                    return $av <=> $bv;
                });
            } elseif($sort === 'prev_desc') {
                usort($groupedData, function($a,$b){
                    $av = $a['prev_variation']; $bv = $b['prev_variation'];
                    if($av === null && $bv === null) return 0;
                    if($av === null) return 1;
                    if($bv === null) return -1;
                    return $bv <=> $av;
                });
            } else {
                usort($groupedData, function($a,$b){
                    return strcmp(strtoupper($a['asset_code']??''), strtoupper($b['asset_code']??''));
                });
            }
            $years = AssetVariation::select('year')->distinct()->orderBy('year','desc')->pluck('year');
            // Variáveis compatíveis com view (variations vazio)
            $variations = collect();
            $prevVariationMap = [];
            return view('openai.variations.index', [
                'variations'=>$variations,
                'years'=>$years,
                'year'=>$year,
                'month'=>$month,
                'code'=>$code,
                'sort'=>$sort,
                'polarity'=>$polarity,
                'prevVariationMap'=>[],
                'change'=>$change,
                'grouped'=>$grouped,
                'sparkWindow'=>$sparkWindow,
                'groupedData'=>$groupedData,
                'trendData'=>[],
                'trendFilter'=>$trendFilter,
                'ppartDay'=>$ppartDay,
                'ppartEndDay'=>$ppartEndDay,
            ]);
        }

        // Modo não agrupado: usar subquery para prev_variation permitindo filtros de mudança e ordenação por diff.
        $q = AssetVariation::with('chat');
    if($applyYear){ $q->where('year',$applyYear); }
    if($applyMonth >= 1 && $applyMonth <= 12){ $q->where('month', $applyMonth); }
        if($code !== ''){ $q->whereRaw('UPPER(asset_code) = ?', [strtoupper($code)]); }
        if ($polarity === 'positive') { $q->where('variation', '>', 0); }
        elseif ($polarity === 'negative') { $q->where('variation', '<', 0); }

        // Subquery compatível com diferentes drivers (MySQL/Postgres usam LIMIT, SQL Server usa TOP)
        $driver = DB::getDriverName();
        if ($driver === 'sqlsrv') {
            $subSql = "select top 1 av2.variation from asset_variations av2\n                where av2.chat_id = asset_variations.chat_id\n                  and ( (asset_variations.month > 1 AND av2.year = asset_variations.year AND av2.month = asset_variations.month - 1)\n                        OR (asset_variations.month = 1 AND av2.year = asset_variations.year - 1 AND av2.month = 12) )";
        } else {
            $subSql = "select av2.variation from asset_variations av2\n                where av2.chat_id = asset_variations.chat_id\n                  and ( (asset_variations.month > 1 AND av2.year = asset_variations.year AND av2.month = asset_variations.month - 1)\n                        OR (asset_variations.month = 1 AND av2.year = asset_variations.year - 1 AND av2.month = 12) )\n                limit 1";
        }
    $q->select('asset_variations.*');
    // selectSub exige string, closure ou builder; passamos a string diretamente
    $q->selectSub($subSql, 'prev_variation');

        // Filtro por mudança (melhoria/piora/igual)
        if($change === 'melhoria'){
            $q->whereRaw('(variation > (' . $subSql . '))');
        } elseif($change === 'piora'){
            $q->whereRaw('(variation < (' . $subSql . '))');
        } elseif($change === 'igual'){
            $q->whereRaw('((' . $subSql . ') IS NOT NULL AND ABS(variation - (' . $subSql . ')) < 1e-12)');
        }

        // Ordenação
        switch($sort){
            case 'variation_asc':
                $q->orderBy('variation','asc')->orderBy('year','desc')->orderBy('month','desc');
                break;
            case 'variation_desc':
                $q->orderBy('variation','desc')->orderBy('year','desc')->orderBy('month','desc');
                break;
            case 'diff_asc':
                $q->orderByRaw('(variation - COALESCE((' . $subSql . '), 0)) asc');
                break;
            case 'diff_desc':
                $q->orderByRaw('(variation - COALESCE((' . $subSql . '), 0)) desc');
                break;
            case 'prev_asc':
                // Ordena por prev_variation (nulls last)
                $q->orderByRaw('CASE WHEN (' . $subSql . ') IS NULL THEN 1 ELSE 0 END asc');
                $q->orderByRaw('(' . $subSql . ') asc');
                break;
            case 'prev_desc':
                $q->orderByRaw('CASE WHEN (' . $subSql . ') IS NULL THEN 1 ELSE 0 END asc');
                $q->orderByRaw('(' . $subSql . ') desc');
                break;
            case 'code_asc':
                $q->orderBy('asset_code','asc')->orderBy('year','desc')->orderBy('month','desc');
                break;
            case 'code_desc':
                $q->orderBy('asset_code','desc')->orderBy('year','desc')->orderBy('month','desc');
                break;
            case 'created_asc':
                $q->orderBy('created_at','asc');
                break;
            case 'created_desc':
                $q->orderBy('created_at','desc');
                break;
            case 'updated_asc':
                $q->orderBy('updated_at','asc');
                break;
            case 'updated_desc':
                $q->orderBy('updated_at','desc');
                break;
            case 'year_asc':
                $q->orderBy('year','asc')->orderBy('month','asc');
                break;
            // Removido case duplicado de year_desc (tratado no default)
            case 'month_asc':
                $q->orderBy('month','asc')->orderBy('year','desc');
                break;
            case 'month_desc':
                $q->orderBy('month','desc')->orderBy('year','desc');
                break;
            case 'year_desc':
            default:
                $q->orderBy('year','desc')->orderBy('month','desc');
        }

        if($noPage){
            // Limite de segurança para evitar estouro de memória
            $all = $q->limit(5000)->get();
            $variations = new \Illuminate\Pagination\LengthAwarePaginator(
                $all,
                $all->count(),
                max(1,$all->count()),
                1,
                ['path'=>request()->url(),'query'=>request()->query()]
            );
        } else {
            $variations = $q->paginate(30);
        }
        $variations = $variations->appends(array_filter([
            'year'=>$request->input('year'),
            // se no_page, não persistir mês para evitar confusão
            'month'=> $noPage ? null : (($month >= 1 && $month <= 12) ? $month : null),
            'code'=>$code?:null,
            'polarity' => in_array($polarity, ['positive','negative']) ? $polarity : null,
            'sort'=>$sort !== 'year_desc' ? $sort : null,
            'change'=> in_array($change, ['melhoria','piora','igual']) ? $change : null,
            'no_page' => $noPage ? 1 : null,
            // Preservar nome do arquivo importado nos links de paginação
            'import_file' => $request->input('import_file'),
        ]));

        // Map prevVariationMap a partir da coluna prev_variation já selecionada
        $prevVariationMap = [];
        $trendData = [];
        // Preparar auxiliar para dias atuais por chat (para mês corrente) usando última data de registro
        $now = Carbon::now();
        $currentMonthChatIds = [];
        foreach($variations as $row){
            $prevVariationMap[$row->id] = $row->prev_variation !== null ? (float)$row->prev_variation : null;
            if($row->year == $now->year && $row->month == $now->month && $row->chat_id){
                $currentMonthChatIds[$row->chat_id] = true;
            }
        }
        $lastDates = [];
        if(!empty($currentMonthChatIds)){
            $records = OpenAIChatRecord::selectRaw('chat_id, MAX(occurred_at) as last_date')
                ->whereIn('chat_id', array_keys($currentMonthChatIds))
                ->whereYear('occurred_at', $now->year)
                ->whereMonth('occurred_at', $now->month)
                ->groupBy('chat_id')
                ->get();
            foreach($records as $r){
                if($r->last_date){
                    $lastDates[$r->chat_id] = Carbon::parse($r->last_date);
                }
            }
        }
        foreach($variations as $row){
            $firstOf = Carbon::create($row->year, $row->month, 1);
            $daysMonth = $firstOf->daysInMonth;
            $daysElapsed = $daysMonth;
            if($row->year == $now->year && $row->month == $now->month){
                $last = $lastDates[$row->chat_id] ?? $now;
                $daysElapsed = min($last->day, $daysMonth);
            }
            $prev = $prevVariationMap[$row->id];
            $curr = (float)$row->variation;
            $trend = $classifyTrend($prev, $curr, $daysElapsed, $daysMonth);
            $trendData[$row->id] = [
                'code' => $trend['code'],
                'label' => $trend['label'],
                'badge' => $trend['badge'],
                'confidence' => $trend['confidence'],
                'normalized' => $trend['normalized'],
                'days_elapsed' => $daysElapsed,
                'days_month' => $daysMonth,
            ];
        }
        // Variação do dia do mês anterior até o fim do mês anterior, ancorada em updated_at
        $prevMonthPartialMap = [];
        $prevMonthPartialRange = [];
        foreach($variations as $row){
            try {
                if(!$row->chat_id){ $prevMonthPartialMap[$row->id] = null; continue; }
                // Se ppartDay e/ou ppartEndDay foram informados, usar controle manual de início/fim relativo ao ano/mês da linha.
                if ($ppartDay !== null || $ppartEndDay !== null) {
                    $firstOfRow = Carbon::create($row->year, $row->month, 1);
                    $prev = $firstOfRow->copy()->subMonthNoOverflow();
                    $pmYear = (int)$prev->year; $pmMonth = (int)$prev->month;
                    $prevDays = (int)$prev->daysInMonth;
                    // fim: se usuário passou ppartEndDay, usar clamp(1..prevDays); senão, se passou só ppartDay, manter sugestão de fim no 30; senão fim no último dia
                    if ($ppartEndDay !== null) {
                        $endDay = (int)max(1, min($ppartEndDay, $prevDays));
                    } elseif ($ppartDay !== null) {
                        $endDay = (int)min(30, $prevDays);
                    } else {
                        $endDay = $prevDays;
                    }
                    // início: se usuário passou ppartDay, clamp ao endDay; senão usar dia do updated/created ancorado e clamp ao endDay
                    if ($ppartDay !== null) {
                        $startBase = (int)$ppartDay;
                    } else {
                        $anchor = $row->updated_at ? Carbon::parse($row->updated_at) : ($row->created_at ? Carbon::parse($row->created_at) : Carbon::now());
                        $startBase = (int)min((int)$anchor->day, $prevDays);
                    }
                    $startDay = (int)max(1, min($startBase, $endDay));
                    $start = Carbon::create($pmYear, $pmMonth, $startDay, 0, 0, 0);
                    $end = Carbon::create($pmYear, $pmMonth, $endDay, 23, 59, 59);
                } else {
                    // Comportamento anterior: ancorado no updated_at/created_at da linha
                    $anchor = $row->updated_at ? Carbon::parse($row->updated_at) : ($row->created_at ? Carbon::parse($row->created_at) : Carbon::now());
                    $prev = $anchor->copy()->subMonthNoOverflow();
                    $pmYear = (int)$prev->year; $pmMonth = (int)$prev->month;
                    $day = (int)min((int)$anchor->day, (int)$prev->daysInMonth);
                    $start = Carbon::create($pmYear, $pmMonth, $day, 0, 0, 0);
                    $end = Carbon::create($pmYear, $pmMonth, (int)$prev->daysInMonth, 23, 59, 59);
                }
                // Preço inicial: primeiro registro em/apos o dia de start dentro do mês anterior
                $startPrice = OpenAIChatRecord::where('chat_id', $row->chat_id)
                    ->whereYear('occurred_at', $pmYear)
                    ->whereMonth('occurred_at', $pmMonth)
                    ->where('occurred_at', '>=', $start)
                    ->orderBy('occurred_at', 'asc')
                    ->value('amount');
                // Preço final: último registro até o fim do mês anterior
                $endPrice = OpenAIChatRecord::where('chat_id', $row->chat_id)
                    ->whereYear('occurred_at', $pmYear)
                    ->whereMonth('occurred_at', $pmMonth)
                    ->where('occurred_at', '<=', $end)
                    ->orderBy('occurred_at', 'desc')
                    ->value('amount');
                $val = null;
                if(is_numeric($startPrice) && is_numeric($endPrice) && (float)$startPrice != 0.0){
                    $val = (( (float)$endPrice - (float)$startPrice) / (float)$startPrice) * 100.0;
                }
                $prevMonthPartialMap[$row->id] = $val;
                $prevMonthPartialRange[$row->id] = [$start->toDateString(), $end->toDateString()];
            } catch(\Throwable $e){
                $prevMonthPartialMap[$row->id] = null;
            }
        }
        // Se houver filtro de tendência, filtrar coleção em memória (MVP; opcionalmente mover para persistência futura)
        if($trendFilter){
            $allowed = (array)explode(',', $trendFilter);
            $items = collect($variations->items())->filter(function($row) use ($trendData,$allowed){
                $t = $trendData[$row->id]['code'] ?? null;
                return in_array($t, $allowed, true);
            })->values();
            // Recriar paginator mantendo metadados originais (simplificação: total passa a ser o count filtrado)
            $variations = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $items->count(),
                $variations->perPage(),
                $variations->currentPage(),
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }
        // Ordenação por Parcial Mês Anterior (ppart_asc/ppart_desc) - em memória
        if(in_array($sort, ['ppart_asc','ppart_desc'], true)){
            $items = collect($variations->items());
            $items = $items->sort(function($a, $b) use ($prevMonthPartialMap, $sort){
                $va = $prevMonthPartialMap[$a->id] ?? null; $vb = $prevMonthPartialMap[$b->id] ?? null;
                $na = is_null($va); $nb = is_null($vb);
                if($na && $nb) return 0; if($na) return 1; if($nb) return -1; // nulls last
                if($sort === 'ppart_asc') return $va <=> $vb; else return $vb <=> $va;
            })->values();
            $variations = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                // manter total original; estamos apenas reordenando a página atual
                $variations->total(),
                $variations->perPage(),
                $variations->currentPage(),
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }
        $years = AssetVariation::select('year')->distinct()->orderBy('year','desc')->pluck('year');

        // Códigos e total USD da carteira do usuário (para seleção e capital padrão)
        $portfolioCodes = [];
        $portfolioUsdTotal = null;
        $portfolioBrlTotal = null;
        $usdToBrlRate = null;
        try {
            $uid = FacadesAuth::id();
            if($uid){
                $holdings = UserHolding::where('user_id', $uid)->get(['code','quantity','current_price']);
                if($holdings->count()){
                    $portfolioCodes = $holdings->pluck('code')->filter()->map(function($c){ return strtoupper(trim((string)$c)); })->unique()->values()->all();
                    $sum = 0.0;
                    foreach($holdings as $h){
                        $q = (float)($h->quantity ?? 0); $p = (float)($h->current_price ?? 0);
                        if($q>0 && $p>0){ $sum += $q*$p; }
                    }
                    if($sum > 0){ $portfolioUsdTotal = $sum; }
                    try {
                        $usdToBrlRate = MoedasValores::where('idmoeda', 1)->orderBy('data','desc')->value('valor');
                        if($usdToBrlRate !== null){ $usdToBrlRate = (float)$usdToBrlRate; }
                    } catch(\Throwable $e){ $usdToBrlRate = null; }
                    if($portfolioUsdTotal && $usdToBrlRate){
                        $portfolioBrlTotal = $portfolioUsdTotal * $usdToBrlRate;
                    }
                }
            }
        } catch(\Throwable $e) { /* noop */ }
        // Rótulo do cabeçalho da coluna Parcial Mês Ant. (ex.: 15/30 ou 28/31)
        try {
            $anchorForHeader = Carbon::now();
            $prevHeader = $anchorForHeader->copy()->subMonthNoOverflow();
            $prevDaysHdr = (int)$prevHeader->daysInMonth;
            // Quando usuário informa fim, usar esse fim; senão, se só início foi informado, manter 30; senão último dia
            if ($ppartEndDay !== null) {
                $hdrLastDay = (int)max(1, min($ppartEndDay, $prevDaysHdr));
            } elseif ($ppartDay !== null) {
                $hdrLastDay = (int)min(30, $prevDaysHdr);
            } else {
                $hdrLastDay = $prevDaysHdr;
            }
            $hdrDay = (int)min((int)($ppartDay ?? $anchorForHeader->day), $hdrLastDay);
            $ppartHeaderLabel = str_pad((string)$hdrDay, 2, '0', STR_PAD_LEFT) . '/' . str_pad((string)$hdrLastDay, 2, '0', STR_PAD_LEFT);
        } catch (\Throwable $e) { $ppartHeaderLabel = null; }

        return view('openai.variations.index', [
            'variations'=>$variations,
            'years'=>$years,
            'year'=>$year,
            'month'=>$month,
            'code'=>$code,
            'sort'=>$sort,
            'polarity'=>$polarity,
            'prevVariationMap'=>$prevVariationMap,
            'change'=>$change,
            'grouped'=>false,
            'sparkWindow'=>$sparkWindow,
            'groupedData'=>[],
            'trendData'=>$trendData,
            'trendFilter'=>$trendFilter,
            'selectedCodes'=> (array) $request->input('selected_codes', []),
            'noPage'=>$noPage,
            'portfolioCodes' => $portfolioCodes,
            'portfolioUsdTotal' => $portfolioUsdTotal,
            'portfolioBrlTotal' => $portfolioBrlTotal,
            'usdToBrlRate' => $usdToBrlRate,
            'prevMonthPartialMap' => $prevMonthPartialMap,
            'prevMonthPartialRange' => $prevMonthPartialRange,
            'ppartHeaderLabel' => $ppartHeaderLabel,
            'ppartDay' => $ppartDay,
            'ppartEndDay' => $ppartEndDay,
        ]);
    }

    public function exportCsv(Request $request)
    {
        $this->authorize('viewAny', OpenAIChat::class);
        $filters = [
            'year' => $request->input('year'),
            'month' => $request->input('month'),
            'code' => $request->input('code'),
            'polarity' => $request->input('polarity'),
            // Permitir exportar apenas selecionados (selected_codes[] via GET)
            'selected_codes' => (array) $request->input('selected_codes', []),
            // Parâmetros de alocação (para export de selecionados com colunas da alocação)
            'capital' => $request->input('capital'),
            'cap_pct' => $request->input('cap_pct'),
            'target_pct' => $request->input('target_pct'),
            'currency' => $request->input('currency'),
            // Dia de âncora para Parcial do Mês Anterior (opcional)
            'ppart_day' => $request->input('ppart_day'),
            // Dia de término opcional para Parcial
            'ppart_end_day' => $request->input('ppart_end_day'),
        ];
        $sort = $request->input('sort', 'year_desc');
        $selectedCodes = array_filter((array)$filters['selected_codes']);
        $capital = $filters['capital'] ?? null;
        // Se vieram selected_codes e capital, usar export de Alocação (uma linha por ativo, colunas da view)
        if (!empty($selectedCodes) && $capital !== null && $capital !== '') {
            $export = new AssetAllocationsExport($filters);
            return Excel::download($export, 'openai-alloc-selected.csv', \Maatwebsite\Excel\Excel::CSV, [
                'Content-Type' => 'text/csv',
            ]);
        }
        $export = new AssetVariationsExport($filters, $sort);
        return Excel::download($export, 'openai-asset-variations.csv', \Maatwebsite\Excel\Excel::CSV, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportXlsx(Request $request)
    {
        $this->authorize('viewAny', OpenAIChat::class);
        $filters = [
            'year' => $request->input('year'),
            'month' => $request->input('month'),
            'code' => $request->input('code'),
            'polarity' => $request->input('polarity'),
            // Permitir exportar apenas selecionados (selected_codes[] via GET)
            'selected_codes' => (array) $request->input('selected_codes', []),
            // Parâmetros de alocação (para export de selecionados com colunas da alocação)
            'capital' => $request->input('capital'),
            'cap_pct' => $request->input('cap_pct'),
            'target_pct' => $request->input('target_pct'),
            'currency' => $request->input('currency'),
            'ppart_day' => $request->input('ppart_day'),
            'ppart_end_day' => $request->input('ppart_end_day'),
        ];
        $sort = $request->input('sort', 'year_desc');
        $selectedCodes = array_filter((array)$filters['selected_codes']);
        $capital = $filters['capital'] ?? null;
        if (!empty($selectedCodes) && $capital !== null && $capital !== '') {
            $export = new AssetAllocationsExport($filters);
            return Excel::download($export, 'openai-alloc-selected.xlsx');
        }
        $export = new AssetVariationsExport($filters, $sort);
        return Excel::download($export, 'openai-asset-variations.xlsx');
    }

    /**
     * Aplica em lote as flags COMPRAR/NÃO COMPRAR por código com base no sinal da variação (%).
     * Considera os filtros atuais (ano, mês, código, polaridade). Para cada código, usa a linha
     * mais recente dentro do conjunto filtrado (maior ano, depois mês) como referência.
     */
    public function batchFlags(Request $request)
    {
        $this->authorize('viewAny', OpenAIChat::class);
        $year = (int) ($request->input('year') ?: 0);
        $monthInput = $request->input('month');
        $month = is_numeric($monthInput) ? (int)$monthInput : 0;
        $code = trim((string)$request->input('code', ''));
        $polarity = (string)$request->input('polarity', ''); // positive|negative|''

        $q = AssetVariation::query();
        if ($year) { $q->where('year', $year); }
        if ($month >= 1 && $month <= 12) { $q->where('month', $month); }
        if ($code !== '') { $q->whereRaw('UPPER(asset_code) = ?', [strtoupper($code)]); }
        if ($polarity === 'positive') { $q->where('variation', '>', 0); }
        elseif ($polarity === 'negative') { $q->where('variation', '<', 0); }

        // Buscar apenas colunas necessárias
        $rows = $q->get(['asset_code','year','month','variation']);
        if ($rows->isEmpty()) {
            return back()->with('info', 'Nada a aplicar: nenhum item encontrado com os filtros atuais.');
        }

        // Escolher por código a linha mais recente (maior ano, depois mês)
        $byCode = [];
        foreach ($rows as $r) {
            $codeKey = strtoupper(trim((string)$r->asset_code));
            if ($codeKey === '') { continue; }
            $key = sprintf('%04d%02d', (int)$r->year, (int)$r->month);
            if (!isset($byCode[$codeKey]) || $key > $byCode[$codeKey]['key']) {
                $byCode[$codeKey] = ['key' => $key, 'variation' => (float)$r->variation];
            }
        }

        $userId = (int) auth()->id();
        $buy = 0; $noBuy = 0; $skipped = 0;
        foreach ($byCode as $codeKey => $info) {
            $v = (float) $info['variation'];
            if ($v > 0) {
                \App\Models\UserAssetFlag::updateOrCreate(
                    ['user_id' => $userId, 'code' => $codeKey],
                    ['no_buy' => 0]
                );
                $buy++;
            } elseif ($v < 0) {
                \App\Models\UserAssetFlag::updateOrCreate(
                    ['user_id' => $userId, 'code' => $codeKey],
                    ['no_buy' => 1]
                );
                $noBuy++;
            } else {
                $skipped++;
            }
        }

        $msg = sprintf('Flags aplicadas: %d COMPRAR, %d NÃO COMPRAR. Ignorados: %d.', $buy, $noBuy, $skipped);
        return back()->with('success', $msg);
    }

    /**
     * Importa um arquivo (CSV ou XLSX) gerado por "Exportar Selecionados" e extrai os códigos
     * para marcar na alocação. Confere a primeira linha do cabeçalho com a frase esperada.
     */
    public function importSelected(Request $request)
    {
        $this->authorize('viewAny', OpenAIChat::class);
        $request->validate([
            'file' => ['required','file','mimes:csv,txt,xlsx']
        ]);

        // Requer que um mês esteja selecionado para evitar importações fora de contexto
        $month = (int) $request->input('month');
        if ($month <= 0 || $month > 12) {
            return back()->withInput()->with('error', 'Selecione um mês antes de importar os selecionados.');
        }

        try {
            $file = $request->file('file');
            // Nome original do arquivo para exibir na UI após o redirect
            $originalName = '';
            try { $originalName = (string) $file->getClientOriginalName(); } catch (\Throwable $e) { $originalName = ''; }
            $import = new \App\Imports\SimpleArrayImport();
            \Maatwebsite\Excel\Facades\Excel::import($import, $file);
            $rows = $import->rows ?? [];
        } catch (\Throwable $e) {
            return back()->with('error', 'Falha ao ler arquivo: '.$e->getMessage());
        }

        if (empty($rows) || !is_array($rows[0] ?? null)) {
            return back()->with('error', 'Arquivo vazio ou formato inválido.');
        }
        // Primeira linha deve conter a mensagem
        $firstRow = $rows[0];
        $firstCell = (string)($firstRow[0] ?? '');
        if (stripos($firstCell, 'Exportado dos registros selecionados CSV/XLSX') === false) {
            return back()->with('error', 'Arquivo não reconhecido: mensagem inicial ausente.');
        }
        // Segunda linha: cabeçalho de colunas; dados a partir da terceira
        $header = $rows[1] ?? [];
        $dataRows = array_slice($rows, 2);
        $codes = [];
        // Detectar coluna de Aloc.Fato, se existir (ex.: "Aloc.Fato (R$)" ou "Aloc.Fato ($US)")
        $allocFatoIdx = null;
        foreach ((array)$header as $idx => $col) {
            $label = strtolower(trim((string)$col));
            if ($label === '') { continue; }
            if (strpos($label, 'aloc.fato') !== false || strpos($label, 'alocfato') !== false) {
                $allocFatoIdx = (int)$idx; break;
            }
        }
        $iyear = (int) ($request->input('year') ?: 0);
        $imonth = (int) $request->input('month');
        $canSaveAllocFato = ($iyear >= 2000 && $iyear <= 2100 && $imonth >= 1 && $imonth <= 12 && $allocFatoIdx !== null);
        $savedAllocCount = 0;
        foreach ($dataRows as $r) {
            $code = strtoupper(trim((string)($r[0] ?? '')));
            if ($code !== '') { $codes[] = $code; }
            if ($canSaveAllocFato && $code !== '') {
                $raw = $r[$allocFatoIdx] ?? null;
                if ($raw !== null && $raw !== '') {
                    $val = $this->parseMoneyLike($raw);
                    if ($val !== null && $val >= 0) {
                        $key = 'openai:variations:alloc_fato:'.$iyear.':'.$imonth.':'.$code;
                        try { Cache::forever($key, (float)$val); $savedAllocCount++; } catch (\Throwable $e) { /* noop */ }
                    }
                }
            }
        }
        $codes = array_values(array_unique(array_filter($codes, fn($c)=> $c !== '')));
        if (empty($codes)) {
            return back()->with('info', 'Nenhum código encontrado no arquivo.');
        }

        // Redirecionar para a listagem com os códigos como selected_codes[] e trigger_alloc=1
        $qs = array_filter([
            'year' => $request->input('year'),
            'month' => $request->input('month'),
            'code' => $request->input('code'),
            'polarity' => $request->input('polarity'),
            'currency' => $request->input('currency'),
            'capital' => $request->input('capital'),
            'cap_pct' => $request->input('cap_pct'),
            'target_pct' => $request->input('target_pct'),
            // Garantir sem paginação após importar selecionados
            'no_page' => 1,
            // Persistir o nome do arquivo na URL para sobreviver a refresh e novas submissões GET
            'import_file' => $originalName ?? null,
        ]);

        $url = route('openai.variations.index', $qs);
        // Anexar selected_codes[] mantendo o restante
        $sep = strpos($url, '?') === false ? '?' : '&';
        foreach ($codes as $c) { $url .= $sep.'selected_codes[]='.urlencode($c); $sep='&'; }
        $successMsg = 'Importação concluída';
        if ($savedAllocCount > 0) { $successMsg .= ' • Aloc.Fato aplicados: '.$savedAllocCount; }
        return redirect($url)
            ->with('success', $successMsg)
            ->with('import_count', count($codes))
            ->with('import_codes', $codes)
            ->with('import_preview', implode(', ', array_slice($codes, 0, 20)).(count($codes)>20?'…':''))
            ->with('import_file_name', $originalName)
            ->with('post_import_clear_alloc', 1);
    }

    /**
     * Faz o parse de valores monetários em formatos comuns
     * aceitando 1.234,56 (pt-BR) ou 1,234.56 (en-US), além de valores simples.
     */
    protected function parseMoneyLike($v): ?float
    {
        if ($v === null) { return null; }
        $s = trim((string)$v);
        if ($s === '') { return null; }
        // remover quaisquer símbolos exceto dígitos, vírgulas, pontos, sinal
        $s = preg_replace('/[^\d,.-]/', '', $s) ?? '';
        if ($s === '') { return null; }
        // Se há . e , assumimos que . é milhar e , é decimal (pt-BR)
        if (strpos($s, '.') !== false && strpos($s, ',') !== false) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } else {
            // Se só tem vírgula, tratar como decimal
            if (strpos($s, ',') !== false && strpos($s, '.') === false) {
                $s = str_replace(',', '.', $s);
            }
        }
        return is_numeric($s) ? (float)$s : null;
    }

    /**
     * Limpa o cache de aplicação para evitar confusão de dados na tela de variações.
     * Mantém a mesma permissão de acesso da área (OPENAI - CHAT).
     */
    public function clearCache(Request $request)
    {
        $this->authorize('viewAny', OpenAIChat::class);
        try {
            Cache::flush();
        } catch (\Throwable $e) {
            return back()->with('error', 'Falha ao limpar cache: '.$e->getMessage());
        }
        return back()->with('success', 'Cache limpo com sucesso.')->with('cache_cleared', 1);
    }

    /**
     * Persiste em cache o valor editável "Aloc.Fato" por (ano, mês, código).
     */
    public function saveAllocFato(Request $request)
    {
        $this->authorize('viewAny', OpenAIChat::class);
        $data = $request->validate([
            'code' => ['required','string','max:50'],
            'year' => ['required','integer','min:2000','max:2100'],
            'month'=> ['required','integer','min:1','max:12'],
            'value'=> ['nullable','numeric','min:0'],
        ]);
        $code = strtoupper(trim((string)$data['code']));
        $key = 'openai:variations:alloc_fato:'.$data['year'].':'.$data['month'].':'.$code;
        if ($data['value'] === null || $data['value'] === '') {
            Cache::forget($key);
        } else {
            // TTL opcional: deixar sem expiração para durar até limpeza manual
            Cache::forever($key, (float)$data['value']);
        }
        return response()->json(['ok'=>true]);
    }
}
