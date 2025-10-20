<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\InvestmentAccountCashEvent;
use App\Models\InvestmentAccountCashSnapshot;
use App\Models\InvestmentAccount;
use Illuminate\Support\Facades\DB;
use App\Services\MarketDataService;
use App\Models\AssetDailyStat;
use Carbon\Carbon;

class InvestmentAccountCashEventController extends Controller
{
    public function index(Request $request)
    {
        $userId = (int)Auth::id();
        $accountId = $request->input('account_id');
        $category = $request->input('category');
        $status = trim((string)$request->input('status'));
    $title = trim((string)$request->input('title'));
        $from = $request->input('from');
        $to = $request->input('to');
        $settleFrom = $request->input('settle_from');
        $settleTo = $request->input('settle_to');
        $direction = $request->input('direction'); // in | out
        $valMin = $request->input('val_min');
        $valMax = $request->input('val_max');
    $source = $request->input('source');
        $sort = $request->input('sort','event_date');
        $dir = strtolower($request->input('dir','desc')) === 'asc' ? 'asc' : 'desc';
    $showRunning = $request->boolean('show_running');

        $allowedSort = [
            'event_date'=>'event_date',
            'settlement_date'=>'settlement_date',
            'category'=>'category',
            'title'=>'title',
            'amount'=>'amount',
            'status'=>'status',
            'source'=>'source'
        ];
        if(!isset($allowedSort[$sort])){ $sort='event_date'; }
        $perPage = min(200, max(10, (int)$request->input('per_page', 50)));

        $q = InvestmentAccountCashEvent::with('account')
            ->where('user_id',$userId);
        if($accountId){ $q->where('account_id',$accountId); }
        if($category){ $q->where('category',$category); }
    if($status!==''){ $q->where('status','LIKE','%'.$status.'%'); }
    if($title!==''){ $q->where('title','LIKE','%'.$title.'%'); }
        if($from){ $q->whereDate('event_date','>=',$from); }
        if($to){ $q->whereDate('event_date','<=',$to); }
        if($settleFrom){ $q->whereDate('settlement_date','>=',$settleFrom); }
        if($settleTo){ $q->whereDate('settlement_date','<=',$settleTo); }
        if($direction==='in') { $q->where('amount','>',0); }
        elseif($direction==='out') { $q->where('amount','<',0); }
        if($valMin !== null && $valMin !== ''){ $q->where('amount','>=',(float)$valMin); }
        if($valMax !== null && $valMax !== ''){ $q->where('amount','<=',(float)$valMax); }
    if($source){ $q->where('source',$source); }
        $q->orderBy($allowedSort[$sort], $dir)->orderBy('id','desc');
        $events = $q->paginate($perPage)->appends($request->query());

        // Totais filtrados (sem paginação)
        $aggregateQuery = clone $q; // clone após filtros (remove orderings para sum)
        $allFiltered = $aggregateQuery->get(['amount']);
        $sumTotal = (float)$allFiltered->sum('amount');
        $sumIn = (float)$allFiltered->filter(fn($e)=>$e->amount>0)->sum('amount');
        $sumOut = (float)$allFiltered->filter(fn($e)=>$e->amount<0)->sum('amount');

        // Resumo por período (mensal) e por conta, com foco em compras, vendas e taxas (fee)
        $periodField = 'event_date'; // padrão: agrupar por data do evento
        $aggRows = (clone $q)->get(['event_date','settlement_date','account_id','category','amount']);
        $periodSummary = [];
        $byAccountSummary = [];
        $accountIdsSeen = [];
        foreach ($aggRows as $row) {
            $cat = strtolower(trim((string)$row->category));
            $amount = (float) $row->amount;
            // Período YYYY-MM por event_date; fallback settlement_date; senão 'sem_data'
            $dt = $row->event_date ?: $row->settlement_date;
            $periodKey = $dt ? $dt->format('Y-m') : 'sem_data';
            if (!isset($periodSummary[$periodKey])) {
                $periodSummary[$periodKey] = ['buy'=>0.0,'sell'=>0.0,'fee'=>0.0];
            }
            $accId = (int)($row->account_id ?: 0);
            if ($accId) { $accountIdsSeen[$accId] = true; }
            if (!isset($byAccountSummary[$accId])) { $byAccountSummary[$accId] = ['buy'=>0.0,'sell'=>0.0,'fee'=>0.0]; }

            // Classificação simples por categoria
            $isFee = (strpos($cat,'fee')!==false) || (strpos($cat,'taxa')!==false) || (strpos($cat,'commission')!==false) || (strpos($cat,'comissão')!==false);
            $isBuy = (strpos($cat,'buy')!==false) || (strpos($cat,'compra')!==false);
            $isSell = (strpos($cat,'sell')!==false) || (strpos($cat,'venda')!==false);

            if ($isFee) {
                $periodSummary[$periodKey]['fee'] += abs($amount);
                $byAccountSummary[$accId]['fee'] += abs($amount);
            } elseif ($isBuy) {
                $periodSummary[$periodKey]['buy'] += abs($amount);
                $byAccountSummary[$accId]['buy'] += abs($amount);
            } elseif ($isSell) {
                $periodSummary[$periodKey]['sell'] += abs($amount);
                $byAccountSummary[$accId]['sell'] += abs($amount);
            }
        }
        // Ordenar períodos desc
        krsort($periodSummary);

        $accounts = InvestmentAccount::where('user_id',$userId)->orderBy('account_name')->get();
        $categories = InvestmentAccountCashEvent::where('user_id',$userId)->distinct()->pluck('category')->sort()->values();
    $sources = InvestmentAccountCashEvent::where('user_id',$userId)->distinct()->pluck('source')->sort()->values();

        // Snapshot mais recente por conta (para eventual exibição rápida)
        $latestSnapshots = InvestmentAccountCashSnapshot::where('user_id',$userId)
            ->selectRaw('account_id, MAX(snapshot_at) as snapshot_at')
            ->groupBy('account_id')
            ->pluck('snapshot_at','account_id');

        // Cálculo de saldo após cada evento (running balance) somente quando filtrado para uma conta e sort relevante
    $canComputeRunning = $showRunning && $accountId && in_array($sort, ['event_date','settlement_date']) && $latestSnapshots->has($accountId) && ($events->currentPage() === 1);
        $runningMode = $canComputeRunning ? $sort.'_'.$dir : null; // para debug/possível future logging
        $latestSnapshotRecord = null; $baseBalance = null; $currentBalance = null;
        if($canComputeRunning){
            $latestSnapshotRecord = InvestmentAccountCashSnapshot::where('user_id',$userId)
                ->where('account_id',$accountId)
                ->orderBy('snapshot_at','desc')
                ->first();
            if($latestSnapshotRecord){
                $currentBalance = $latestSnapshotRecord->available_amount;
                $baseBalance = $currentBalance - $sumTotal;
                // Buscar todos eventos filtrados (limite de segurança)
                $orderCol = $sort === 'settlement_date' ? 'settlement_date' : 'event_date';
                $allForRunning = (clone $q)
                    ->select(['id','amount',$orderCol])
                    ->orderBy($orderCol,'asc')
                    ->orderBy('id','asc')
                    ->limit(10000) // segurança contra explosão
                    ->get();
                $running = $baseBalance;
                $mapRunning = [];
                foreach($allForRunning as $evAll){
                    $running += $evAll->amount;
                    $mapRunning[$evAll->id] = $running; // saldo após este evento
                }
                foreach($events as $ev){
                    $ev->running_balance_after = $mapRunning[$ev->id] ?? null;
                }
            } else {
                $canComputeRunning = false;
                foreach($events as $ev){ $ev->running_balance_after = null; }
            }
        } else {
            foreach($events as $ev){ $ev->running_balance_after = null; }
        }

        return view('portfolio.cash_events_index', [
            'events'=>$events,
            'accounts'=>$accounts,
            'categories'=>$categories,
            'periodSummary'=>$periodSummary,
            'byAccountSummary'=>$byAccountSummary,
            'filter_account_id'=>$accountId ? (int)$accountId : null,
            'filter_category'=>$category,
            'filter_status'=>$status,
            'filter_title'=>$title,
            'filter_from'=>$from,
            'filter_to'=>$to,
            'filter_settle_from'=>$settleFrom,
            'filter_settle_to'=>$settleTo,
            'filter_direction'=>$direction,
            'filter_val_min'=>$valMin,
            'filter_val_max'=>$valMax,
            'filter_source'=>$source,
            'sort'=>$sort,
            'dir'=>$dir,
            'sumTotal'=>$sumTotal,
            'sumIn'=>$sumIn,
            'sumOut'=>$sumOut,
            'latestSnapshots'=>$latestSnapshots,
            'perPage'=>$perPage,
            'sources'=>$sources,
            'canComputeRunning'=>$canComputeRunning,
            'showRunning'=>$showRunning,
        ]);
    }

    public function exportCsv(Request $request)
    {
        $userId = (int)Auth::id();
        $accountId = $request->input('account_id');
        $category = $request->input('category');
        $status = trim((string)$request->input('status'));
        $from = $request->input('from');
        $to = $request->input('to');

        $q = InvestmentAccountCashEvent::where('user_id',$userId);
        if($accountId){ $q->where('account_id',$accountId); }
        if($category){ $q->where('category',$category); }
        if($status!==''){ $q->where('status','LIKE','%'.$status.'%'); }
        if($from){ $q->whereDate('event_date','>=',$from); }
        if($to){ $q->whereDate('event_date','<=',$to); }
        $q->orderBy('event_date','desc')->orderBy('id','desc');

        $filename = 'cash_events_'.date('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"'
        ];
        $callback = function() use ($q){
            $out = fopen('php://output','w');
            // BOM UTF-8
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['event_date','settlement_date','account_id','category','title','detail','amount','currency','status'], ';');
            $q->chunk(1000, function($chunk) use ($out){
                foreach($chunk as $e){
                    fputcsv($out, [
                        optional($e->event_date)->format('Y-m-d'),
                        optional($e->settlement_date)->format('Y-m-d'),
                        $e->account_id,
                        $e->category,
                        $e->title,
                        $e->detail,
                        number_format($e->amount,6,'.',''),
                        $e->currency,
                        $e->status,
                    ], ';');
                }
            });
            fclose($out);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function truncateUserData(Request $request)
    {
        $userId = (int)Auth::id();
        // Proteção simples: requer campo hidden confirm=yes
        if($request->input('confirm') !== 'yes'){
            return back()->with('error','Confirmação ausente.');
        }
        if($request->input('confirm_token') !== 'APAGAR'){
            return back()->with('error','Digite APAGAR exatamente para confirmar.');
        }
        $eventsDeleted = 0; $snapsDeleted = 0;
        DB::transaction(function() use ($userId, &$eventsDeleted, &$snapsDeleted){
            // Apaga apenas dados do usuário atual (segurança multi-tenant)
            $eventsDeleted = DB::table('investment_account_cash_events')->where('user_id',$userId)->delete();
            $snapsDeleted = DB::table('investment_account_cash_snapshots')->where('user_id',$userId)->delete();
        });
        return redirect()->route('cash.events.index')
            ->with('success', "Eventos de caixa limpos: {$eventsDeleted}, snapshots: {$snapsDeleted}.");
    }

    /**
     * Resumo por Ativo: SALDO (quantidade) e SALDO MÉDIO (preço médio) considerando eventos filtrados.
     * Regras:
     * - Identifica compras e vendas parsing o título/detalhe: exemplos pt/inglês
     *   "Compra de 2 DVN a $ 32,12 cada" => buy, qty=2, symbol=DVN, unit=32.12
     *   "Venda de 1 AAPL a $ 190.50" => sell, qty=1, symbol=AAPL, unit=190.50
     * - Mantém preço médio móvel: compras ajustam custo médio, vendas reduzem quantidade e custo proporcional
     * - Filtros compatíveis com index(): account_id, from, to, settle_from, settle_to, source
     */
    public function positionsSummary(Request $request)
    {
        $userId = (int)Auth::id();
        $accountId = $request->input('account_id');
        $from = $request->input('from');
        $to = $request->input('to');
        $settleFrom = $request->input('settle_from');
        $settleTo = $request->input('settle_to');
    $source = $request->input('source');
    // Padrão agora é 'db' (usar último registro persistido) quando não for informado
    $quoteMode = $request->input('quote_mode', 'db'); // 'api' | 'db'

        // Base query: mesmos filtros de período/fonte
        $q = InvestmentAccountCashEvent::where('user_id',$userId);
        if($accountId){ $q->where('account_id',$accountId); }
        if($from){ $q->whereDate('event_date','>=',$from); }
        if($to){ $q->whereDate('event_date','<=',$to); }
        if($settleFrom){ $q->whereDate('settlement_date','>=',$settleFrom); }
        if($settleTo){ $q->whereDate('settlement_date','<=',$settleTo); }
        if($source){ $q->where('source',$source); }

        // Ordem cronológica para média móvel consistente
        $events = $q->orderBy('event_date','asc')->orderBy('id','asc')->get(['event_date','title','detail','category','amount']);

        // Parser: retorna [type=>'buy'|'sell', symbol, qty, unit]
        $parse = function($title, $detail){
            $txt = trim((string)($title ?: ''));
            if($detail){ $txt .= ' '.trim((string)$detail); }
            $t = mb_strtoupper($txt,'UTF-8');
            // Normaliza separadores decimais e símbolos
            $norm = preg_replace('/\s+/', ' ', $t);
            // Padrões em PT: COMPRA|VENDA de 2 DVN a $ 32,12 cada
            if(preg_match('/\b(COMPRA|VENDA)\b\s+DE\s+(\d+[\.,]?\d*)\s+([A-Z0-9\.\-:_]+)\s+A\s*\$\s*(\d+[\.,]?\d*)/u', $norm, $m)){
                $type = ($m[1]==='COMPRA')?'buy':'sell';
                $qty = (float)str_replace(',','.', str_replace('.','', $m[2]));
                $sym = trim($m[3]);
                $unit = (float)str_replace(',','.', str_replace('.','', $m[4]));
                return compact('type','sym') + ['qty'=>$qty,'unit'=>$unit];
            }
            // Padrões EN: BUY 2 DVN @ 32.12, SELL 1 AAPL AT $190.50
            if(preg_match('/\b(BUY|SELL)\b\s+(\d+[\.,]?\d*)\s+([A-Z0-9\.\-:_]+)\s+(@|AT)\s*\$?\s*(\d+[\.,]?\d*)/u', $norm, $m)){
                $type = ($m[1]==='BUY')?'buy':'sell';
                $qty = (float)str_replace(',','.', str_replace('.','', $m[2]));
                $sym = trim($m[3]);
                $unit = (float)str_replace(',','.', str_replace('.','', $m[5]));
                return compact('type','sym') + ['qty'=>$qty,'unit'=>$unit];
            }
            return null;
        };

        // Agregadores por ativo
        $map = [];
        foreach($events as $ev){
            $p = $parse($ev->title, $ev->detail);
            if(!$p) continue;
            $sym = $p['sym'];
            if(!isset($map[$sym])){ $map[$sym] = ['symbol'=>$sym,'qty'=>0.0,'avg'=>0.0,'cost'=>0.0]; }
            $qty = (float)$p['qty'];
            $unit = (float)$p['unit'];
            if($qty <= 0 || $unit <= 0) continue;
            if($p['type'] === 'buy'){
                // média móvel: novo custo = custo + qty*unit; nova qty = qty +
                $map[$sym]['cost'] += $qty * $unit;
                $map[$sym]['qty'] += $qty;
                $map[$sym]['avg'] = $map[$sym]['qty']>0 ? $map[$sym]['cost'] / $map[$sym]['qty'] : 0.0;
            } else {
                // venda: reduz posição; custo proporcional sai
                $sellQty = min($qty, max(0.0, $map[$sym]['qty']));
                if($sellQty > 0){
                    $proportionalCost = $map[$sym]['avg'] * $sellQty;
                    $map[$sym]['qty'] -= $sellQty;
                    $map[$sym]['cost'] = max(0.0, $map[$sym]['cost'] - $proportionalCost);
                    $map[$sym]['avg'] = $map[$sym]['qty']>0 ? $map[$sym]['cost'] / $map[$sym]['qty'] : 0.0;
                }
            }
        }

        // Ordena por símbolo
        ksort($map, SORT_NATURAL | SORT_FLAG_CASE);

        // Enriquecer com última cotação para DOIS modos (db e api) e permitir alternância client-side sem recomputar posições
        /** @var MarketDataService $md */
        $md = app(MarketDataService::class);

        $computeWithMode = function(array $baseMap, string $mode) use ($md) {
            $positions = [];
            $variationTotalsLocal = [];
            foreach ($baseMap as $sym => $posBase) {
                $pos = $posBase; // cópia
                $qty = (float)($pos['qty'] ?? 0);
                if ($qty <= 0) {
                    $pos['current_price'] = null;
                    $pos['currency'] = null;
                    $pos['updated_at'] = null;
                    $pos['quote_source'] = null;
                    $pos['quote_mode'] = $mode;
                    $pos['new_total'] = 0.0;
                    $pos['variation'] = 0.0;
                    $pos['variation_pct'] = null;
                    $positions[$sym] = $pos;
                    continue;
                }
                $price = null; $currency = null; $updatedAt = null; $quoteSource = null; $modeUsed = $mode;

                if ($mode === 'db') {
                    $dbStat = AssetDailyStat::where('symbol', strtoupper($sym))
                        ->orderBy('date', 'desc')
                        ->first();
                    if ($dbStat) {
                        $price = is_numeric($dbStat->close_value) ? (float) $dbStat->close_value : null;
                        $updatedAt = optional($dbStat->date)->format('Y-m-d');
                        $currency = (str_ends_with(strtoupper($sym), '.SA') ? 'BRL' : 'USD');
                        $quoteSource = 'db_asset_daily_stats';
                    }
                    if ($price === null) {
                        $modeUsed = 'api';
                        try {
                            $q = $md->getQuote($sym);
                        } catch (\Throwable $e) {
                            $q = ['symbol'=>$sym,'price'=>null,'currency'=>null,'updated_at'=>null,'source'=>'none'];
                        }
                        $price = is_numeric($q['price'] ?? null) ? (float)$q['price'] : null;
                        $currency = $q['currency'] ?? $currency;
                        $updatedAt = $q['updated_at'] ?? $updatedAt;
                        $quoteSource = $q['source'] ?? 'none';
                        if ($price !== null) {
                            try {
                                $today = Carbon::today();
                                $existing = AssetDailyStat::where('symbol', strtoupper($sym))
                                    ->whereDate('date', '=', $today->format('Y-m-d'))
                                    ->first();
                                if ($existing) {
                                    $existing->close_value = $price;
                                    $existing->is_accurate = true;
                                    $existing->save();
                                } else {
                                    AssetDailyStat::create([
                                        'symbol' => strtoupper($sym),
                                        'date' => Carbon::now(),
                                        'close_value' => $price,
                                        'is_accurate' => true,
                                    ]);
                                }
                            } catch (\Throwable $t) { /* noop persist */ }
                        }
                    }
                } else { // api
                    try {
                        $q = $md->getQuote($sym);
                    } catch (\Throwable $e) {
                        $q = ['symbol'=>$sym,'price'=>null,'currency'=>null,'updated_at'=>null,'source'=>'none'];
                    }
                    $price = is_numeric($q['price'] ?? null) ? (float)$q['price'] : null;
                    $currency = $q['currency'] ?? null;
                    $updatedAt = $q['updated_at'] ?? null;
                    $quoteSource = $q['source'] ?? null;
                    if ($price !== null) {
                        try {
                            $today = Carbon::today();
                            $existing = AssetDailyStat::where('symbol', strtoupper($sym))
                                ->whereDate('date', '=', $today->format('Y-m-d'))
                                ->first();
                            if ($existing) {
                                $existing->close_value = $price;
                                $existing->is_accurate = true;
                                $existing->save();
                            } else {
                                AssetDailyStat::create([
                                    'symbol' => strtoupper($sym),
                                    'date' => Carbon::now(),
                                    'close_value' => $price,
                                    'is_accurate' => true,
                                ]);
                            }
                        } catch (\Throwable $t) { /* noop persist */ }
                    }
                }

                $pos['current_price'] = $price;
                $pos['currency'] = $currency;
                $pos['updated_at'] = $updatedAt;
                $pos['quote_source'] = $quoteSource;
                $pos['quote_mode'] = $modeUsed;
                if ($price !== null) {
                    $newTotal = $qty * $price;
                    $pos['new_total'] = $newTotal;
                    $oldCost = (float)($pos['cost'] ?? 0.0);
                    $var = $newTotal - $oldCost;
                    $pos['variation'] = $var;
                    $pos['variation_pct'] = ($oldCost > 0.0) ? ($var / $oldCost) : null;
                    $cur = $pos['currency'] ?: 'USD';
                    if (!isset($variationTotalsLocal[$cur])) {
                        $variationTotalsLocal[$cur] = ['positive' => 0.0, 'negative' => 0.0];
                    }
                    if ($var > 0) { $variationTotalsLocal[$cur]['positive'] += $var; }
                    if ($var < 0) { $variationTotalsLocal[$cur]['negative'] += $var; }
                } else {
                    $pos['new_total'] = null;
                    $pos['variation'] = null;
                    $pos['variation_pct'] = null;
                }
                $positions[$sym] = $pos;
            }
            // diferença por moeda
            foreach ($variationTotalsLocal as $cur => $vals) {
                $variationTotalsLocal[$cur]['difference'] = ($vals['positive'] + $vals['negative']);
            }
            // Ordena por símbolo para estabilidade de exibição
            ksort($positions, SORT_NATURAL | SORT_FLAG_CASE);
            return [$positions, $variationTotalsLocal];
        };

        // Computa datasets para ambos os modos para alternância instantânea no front-end
        [$positionsDb, $variationTotalsDb] = $computeWithMode($map, 'db');
        [$positionsApi, $variationTotalsApi] = $computeWithMode($map, 'api');

        // Escolhe conjunto inicial conforme query
        $positions = ($quoteMode === 'db') ? $positionsDb : $positionsApi;
        $variationTotals = ($quoteMode === 'db') ? $variationTotalsDb : $variationTotalsApi;

        // Dados auxiliares de filtros
        $accounts = InvestmentAccount::where('user_id',$userId)->orderBy('account_name')->get();
        $sources = InvestmentAccountCashEvent::where('user_id',$userId)->distinct()->pluck('source')->sort()->values();

        return view('portfolio.cash_positions_summary', [
            'positions' => $positions,
            'accounts' => $accounts,
            'sources' => $sources,
            'quote_mode' => $quoteMode,
            'filter_account_id' => $accountId ? (int)$accountId : null,
            'filter_from' => $from,
            'filter_to' => $to,
            'filter_settle_from' => $settleFrom,
            'filter_settle_to' => $settleTo,
            'filter_source' => $source,
            'variationTotals' => $variationTotals,
            // Datasets para alternância client-side
            'positionsDb' => array_values($positionsDb),
            'positionsApi' => array_values($positionsApi),
            'variationTotalsDb' => $variationTotalsDb,
            'variationTotalsApi' => $variationTotalsApi,
        ]);
    }
}
