<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetVariation;
use App\Models\OpenAIChat;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AssetVariationsExport;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\OpenAIChatRecord;

class AssetVariationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:OPENAI - CHAT'])->only('index','store','exportCsv','exportXlsx','batchFlags');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', OpenAIChat::class);
        $year = (int)($request->input('year') ?: date('Y'));
        $monthInput = $request->input('month');
        $month = is_numeric($monthInput) ? (int)$monthInput : 0; // 1..12
        $code = trim($request->input('code',''));
        $polarity = $request->input('polarity'); // positive | negative
    $change = $request->input('change'); // melhoria|piora|igual|null
    $trendFilter = $request->input('trend'); // códigos de tendência
        $grouped = (bool)$request->boolean('grouped');
        $sparkWindow = (int)($request->input('spark_window') ?: 6);
        if($sparkWindow < 3) $sparkWindow = 3; if($sparkWindow > 24) $sparkWindow = 24;
    $sort = $request->input('sort', 'year_desc'); // adiciona diff_asc|diff_desc|prev_asc|prev_desc
        $noPage = $request->boolean('no_page');

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
            ]);
        }

        // Modo não agrupado: usar subquery para prev_variation permitindo filtros de mudança e ordenação por diff.
        $q = AssetVariation::with('chat');
        if($year){ $q->where('year',$year); }
        if($month >= 1 && $month <= 12){ $q->where('month', $month); }
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
            case 'year_desc':
                $q->orderBy('year','desc')->orderBy('month','desc');
                break;
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
            'month'=> ($month >= 1 && $month <= 12) ? $month : null,
            'code'=>$code?:null,
            'polarity' => in_array($polarity, ['positive','negative']) ? $polarity : null,
            'sort'=>$sort !== 'year_desc' ? $sort : null,
            'change'=> in_array($change, ['melhoria','piora','igual']) ? $change : null,
            'no_page' => $noPage ? 1 : null,
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
        $years = AssetVariation::select('year')->distinct()->orderBy('year','desc')->pluck('year');
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
        ];
        $sort = $request->input('sort', 'year_desc');
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
        ];
        $sort = $request->input('sort', 'year_desc');
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
}
