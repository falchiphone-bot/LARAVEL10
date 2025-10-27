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
use Illuminate\Pagination\LengthAwarePaginator;

class InvestmentAccountCashEventController extends Controller
{
    /**
     * Normaliza um número decimal aceitando formatos pt-BR (1.234,56) e en-US (1,234.56 / 1234.56).
     * Remove espaços/NBSP, decide separador decimal pela última ocorrência entre ',' e '.'.
     */
    private function parseDecimalFlexible($raw): ?float
    {
        $s = trim((string)$raw);
        if ($s === '') return null;
        // remove espaços regulares e NBSP
        $s = str_replace(["\xC2\xA0", chr(160), ' '], '', $s);
        $hasDot = strpos($s, '.') !== false;
        $hasComma = strpos($s, ',') !== false;
        if ($hasDot && $hasComma) {
            $lastDot = strrpos($s, '.');
            $lastComma = strrpos($s, ',');
            if ($lastComma > $lastDot) {
                // vírgula é separador decimal => remove pontos (milhar) e troca vírgula por ponto
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
            } else {
                // ponto é separador decimal => remove vírgulas (milhar)
                $s = str_replace(',', '', $s);
            }
        } elseif ($hasComma) {
            // somente vírgula => trata como decimal
            $s = str_replace('.', '', $s); // se houver ponto como milhar, remove
            $s = str_replace(',', '.', $s);
        } else {
            // somente ponto ou apenas dígitos -> mantém
        }
        if (!is_numeric($s)) return null;
        return (float)$s;
    }
    public function updateInline(Request $request)
    {
        $userId = (int)Auth::id();
        $targets = (array)$request->input('target_amount', []);
        $probs = (array)$request->input('target_probability_pct', []);

        // Coleção de IDs a atualizar
        $ids = array_unique(array_filter(array_map('intval', array_merge(array_keys($targets), array_keys($probs))), fn($v)=>$v>0));
        if (empty($ids)) {
            return redirect()->back()->with('info', 'Nada para salvar.');
        }

        $updated = 0;
        $events = InvestmentAccountCashEvent::where('user_id',$userId)->whereIn('id',$ids)->get();
        foreach ($events as $ev) {
            $id = $ev->id;
            $hasChange = false;
            if (array_key_exists($id, $targets)) {
                $raw = $targets[$id];
                $val = ($raw === '' || $raw === null) ? null : $this->parseDecimalFlexible($raw);
                if ($ev->target_amount !== $val) { $ev->target_amount = $val; $hasChange = true; }
            }
            if (array_key_exists($id, $probs)) {
                $raw = $probs[$id];
                $p = ($raw === '' || $raw === null) ? null : $this->parseDecimalFlexible(str_replace('%','', (string)$raw));
                if ($p !== null) { $p = max(0.0, min(100.0, $p)); }
                if ($ev->target_probability_pct !== $p) { $ev->target_probability_pct = $p; $hasChange = true; }
            }
            if ($hasChange) { $ev->save(); $updated++; }
        }

        return redirect()->back()->with('success', "Metas atualizadas em {$updated} evento(s).");
    }
    public function index(Request $request)
    {
        $userId = (int)Auth::id();
        $accountId = $request->input('account_id');
        $category = $request->input('category');
        $status = trim((string)$request->input('status'));
    $filterTitle = trim((string)$request->input('title'));
        $from = $request->input('from');
        $to = $request->input('to');
        $settleFrom = $request->input('settle_from');
        $settleTo = $request->input('settle_to');
    $direction = $request->input('direction'); // in | out
    $buySell = $request->input('buy_sell'); // buy | sell | null
    $hasMeta = $request->boolean('has_meta'); // somente com meta preenchida
        $valMin = $request->input('val_min');
        $valMax = $request->input('val_max');
    $source = $request->input('source');
        $sort = $request->input('sort','event_date');
        $dir = strtolower($request->input('dir','desc')) === 'asc' ? 'asc' : 'desc';
    $showRunning = $request->boolean('show_running');
    $groupAsset = $request->boolean('group_asset');
        $paginate = $request->boolean('paginate', true);
        $onlyBuySell = $request->boolean('only_buy_sell', false);

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
    // Limite de página: quando paginar, força 5000 e trava na UI
    $perPage = (int)$request->input('per_page', 50);
    if ($paginate) { $perPage = 5000; }
    else { $perPage = min(5000, max(10, $perPage)); }

        $q = InvestmentAccountCashEvent::with('account')
            ->where('user_id',$userId);
        if($accountId){ $q->where('account_id',$accountId); }
        if($category){ $q->where('category',$category); }
    if($status!==''){ $q->where('status','LIKE','%'.$status.'%'); }
    if($filterTitle!==''){ $q->where('title','LIKE','%'.$filterTitle.'%'); }
        if($from){ $q->whereDate('event_date','>=',$from); }
        if($to){ $q->whereDate('event_date','<=',$to); }
        if($settleFrom){ $q->whereDate('settlement_date','>=',$settleFrom); }
        if($settleTo){ $q->whereDate('settlement_date','<=',$settleTo); }
        if($direction==='in') { $q->where('amount','>',0); }
        elseif($direction==='out') { $q->where('amount','<',0); }
        if($valMin !== null && $valMin !== ''){ $q->where('amount','>=',(float)$valMin); }
        if($valMax !== null && $valMax !== ''){ $q->where('amount','<=',(float)$valMax); }
    if($source){ $q->where('source',$source); }
        if ($hasMeta) { $q->whereNotNull('target_amount'); }
        if ($buySell === 'buy') {
            $q->where(function($w){
                $w->where('category','LIKE','%compra%')
                  ->orWhere('title','LIKE','%compra%')
                  ->orWhere('title','LIKE','%BUY%')
                  ->orWhere('detail','LIKE','%compra%')
                  ->orWhere('detail','LIKE','%BUY%');
            });
        } elseif ($buySell === 'sell') {
            $q->where(function($w){
                $w->where('category','LIKE','%venda%')
                  ->orWhere('title','LIKE','%venda%')
                  ->orWhere('title','LIKE','%SELL%')
                  ->orWhere('detail','LIKE','%venda%')
                  ->orWhere('detail','LIKE','%SELL%');
            });
        } elseif ($onlyBuySell) {
            $q->where(function($w){
                $w->where('category','LIKE','%compra%')
                  ->orWhere('category','LIKE','%venda%')
                  ->orWhere('title','LIKE','%compra%')
                  ->orWhere('title','LIKE','%venda%')
                  ->orWhere('title','LIKE','%BUY%')
                  ->orWhere('title','LIKE','%SELL%')
                  ->orWhere('detail','LIKE','%compra%')
                  ->orWhere('detail','LIKE','%venda%')
                  ->orWhere('detail','LIKE','%BUY%')
                  ->orWhere('detail','LIKE','%SELL%');
            });
        }
        $q->orderBy($allowedSort[$sort], $dir)->orderBy('id','desc');
        if ($paginate) {
            $events = $q->paginate($perPage)->appends($request->query());
        } else {
            // Sem paginação: retorna todos os eventos filtrados
            $all = $q->get();
            $events = new LengthAwarePaginator(
                $all,
                $all->count(),
                $all->count() > 0 ? $all->count() : 1,
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        // Derivar preço base por unidade e símbolo/quantidade (quando possível) a partir do título/detalhe,
        // para usar na coluna Meta Δ (%) e no cálculo de Total Atual (preço atual * quantidade).
        $parseUnit = function($title, $detail){
            $txt = trim((string)($title ?: ''));
            if($detail){ $txt .= ' '.trim((string)$detail); }
            $t = mb_strtoupper($txt,'UTF-8');
            $norm = preg_replace('/\s+/', ' ', $t);
            // PT: COMPRA|VENDA de 2 DVN a $ 32,12 cada
            if(preg_match('/\b(COMPRA|VENDA)\b\s+DE\s+(\d+[\.,]?\d*)\s+([A-Z0-9\.-:_]+)\s+A\s*\$\s*(\d+[\.,]?\d*)/u', $norm, $m)){
                $qty = (float)str_replace(',', '.', str_replace('.', '', $m[2]));
                $sym = trim($m[3]);
                $unit = (float)str_replace(',', '.', str_replace('.', '', $m[4]));
                return ['qty'=>$qty>0?$qty:null, 'unit'=>$unit>0?$unit:null, 'sym'=>$sym!==''?$sym:null];
            }
            // EN: BUY 2 DVN @ 32.12, SELL 1 AAPL AT $190.50
            if(preg_match('/\b(BUY|SELL)\b\s+(\d+[\.,]?\d*)\s+([A-Z0-9\.-:_]+)\s+(@|AT)\s*\$?\s*(\d+[\.,]?\d*)/u', $norm, $m)){
                $qty = (float)str_replace(',', '.', str_replace('.', '', $m[2]));
                $sym = trim($m[3]);
                $unit = (float)str_replace(',', '.', str_replace('.', '', $m[5]));
                return ['qty'=>$qty>0?$qty:null, 'unit'=>$unit>0?$unit:null, 'sym'=>$sym!==''?$sym:null];
            }
            return null;
        };
        $symbolsWanted = [];
        foreach ($events as $e) {
            try {
                $evTitle = (string)($e->title ?? '');
                $evDetail = (string)($e->detail ?? '');
                $evCategory = (string)($e->category ?? '');
                $parsed = $parseUnit($evTitle, $evDetail);
                $qty = $parsed['qty'] ?? null; $unit = $parsed['unit'] ?? null;
                if ($unit !== null && $unit > 0) {
                    $e->setAttribute('unit_base_price', (float)$unit);
                } elseif ($qty !== null && $qty > 0 && is_numeric($e->amount)) {
                    $e->setAttribute('unit_base_price', abs((float)$e->amount) / (float)$qty);
                } else {
                    $e->setAttribute('unit_base_price', null);
                }
                // Quantidade e símbolo extraídos (para calcular Total Atual)
                if ($qty !== null && $qty > 0) { $e->setAttribute('parsed_qty', (float)$qty); }
                else { $e->setAttribute('parsed_qty', null); }
                $sym = $parsed['sym'] ?? null;
                if ($sym) {
                    $symUp = strtoupper(trim($sym));
                    $e->setAttribute('parsed_symbol', $symUp);
                    $symbolsWanted[$symUp] = true;
                } else {
                    $e->setAttribute('parsed_symbol', null);
                }
                // Classificação simples de compra/venda por texto (pt/en)
                $txt = mb_strtolower($evCategory.' '.$evTitle.' '.$evDetail, 'UTF-8');
                $isBuy = (str_contains($txt,'compra') || str_contains($txt,'buy'));
                $isSell = (str_contains($txt,'venda') || str_contains($txt,'sell'));
                $e->setAttribute('is_buy', $isBuy);
                $e->setAttribute('is_sell', $isSell);
            } catch (\Throwable $t) {
                $e->setAttribute('unit_base_price', null);
                $e->setAttribute('is_buy', false);
                $e->setAttribute('is_sell', false);
                $e->setAttribute('parsed_qty', null);
                $e->setAttribute('parsed_symbol', null);
            }
        }

        // Buscar preço atual por código (último OpenAIChatRecord por código do usuário)
        $currentPriceByCode = [];
        if (!empty($symbolsWanted)) {
            $codesUpper = array_keys($symbolsWanted);
            // Normaliza expressão por driver (SQL Server vs outros)
            $driverOP = DB::getDriverName();
            $codeExpr = ($driverOP === 'sqlsrv') ? "UPPER(LTRIM(RTRIM(c.code)))" : "UPPER(TRIM(c.code))";
            $rows = DB::table('openai_chat_records as r')
                ->join('open_a_i_chats as c', 'c.id', '=', 'r.chat_id')
                ->where('r.user_id', $userId)
                ->whereIn(DB::raw($codeExpr), $codesUpper)
                ->orderBy('r.occurred_at','desc')
                ->orderBy('r.id','desc')
                ->selectRaw($codeExpr.' as code, r.amount, r.occurred_at')
                ->get();
            foreach ($rows as $row) {
                $code = strtoupper(trim((string)($row->code ?? '')));
                if ($code === '') continue;
                if (!isset($currentPriceByCode[$code])) {
                    $amt = is_numeric($row->amount ?? null) ? (float)$row->amount : null;
                    if ($amt !== null) { $currentPriceByCode[$code] = $amt; }
                }
            }
        }
        // Atribuir preço atual e total atual por linha (para compra e venda):
        // - current_price: sempre que houver símbolo e preço recente encontrado
        // - current_total: quando houver também quantidade (>0)
        foreach ($events as $e) {
            $e->setAttribute('current_price', null);
            $e->setAttribute('current_total', null);
            $sym = (string)($e->parsed_symbol ?? '');
            if ($sym !== '' && !empty($currentPriceByCode)) {
                $codeUp = strtoupper($sym);
                $price = $currentPriceByCode[$codeUp] ?? null;
                if ($price !== null) {
                    $e->setAttribute('current_price', (float)$price);
                    $qty = is_numeric($e->parsed_qty ?? null) ? (float)$e->parsed_qty : null;
                    if ($qty !== null && $qty > 0) {
                        $e->setAttribute('current_total', (float)$price * (float)$qty);
                    }
                }
            }
        }

        // Totais filtrados (sem paginação)
        $aggregateQuery = clone $q; // clone após filtros (remove orderings para sum)
        $allFiltered = $aggregateQuery->get(['amount']);
        $sumTotal = (float)$allFiltered->sum('amount');
        $sumIn = (float)$allFiltered->filter(fn($e)=>$e->amount>0)->sum('amount');
        $sumOut = (float)$allFiltered->filter(fn($e)=>$e->amount<0)->sum('amount');

        // Resumo por período (mensal) e por conta, com foco em compras, vendas e taxas (fee)
        $periodField = 'event_date'; // padrão: agrupar por data do evento
    $aggRows = (clone $q)->get(['event_date','settlement_date','account_id','category','amount','title','detail']);
        $periodSummary = [];
        $byAccountSummary = [];
    $byAssetSummary = [];
        $accountIdsSeen = [];
    // Soma de quantidade quando filtrado por título e Tipo=Compra/Venda
    $buyQtySum = null; $sellQtySum = null;
    if ($buySell === 'buy' && $filterTitle !== '') { $buyQtySum = 0.0; }
    if ($buySell === 'sell' && $filterTitle !== '') { $sellQtySum = 0.0; }
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

            // Soma de quantidade de compras quando buySell=buy e título filtrado
            if ($buyQtySum !== null) {
                $parserQ = function($title, $detail){
                    $txt = trim((string)($title ?: ''));
                    if($detail){ $txt .= ' '.trim((string)$detail); }
                    $t = mb_strtoupper($txt,'UTF-8');
                    $norm = preg_replace('/\s+/', ' ', $t);
                    if(preg_match('/\b(COMPRA)\b\s+DE\s+(\d+[\.,]?\d*)\s+[A-Z0-9\.-:_]+\s+A\s*\$\s*(\d+[\.,]?\d*)/u', $norm, $m)){
                        $qty = (float)str_replace(',', '.', str_replace('.', '', $m[2]));
                        return $qty>0 ? $qty : null;
                    }
                    if(preg_match('/\b(BUY)\b\s+(\d+[\.,]?\d*)\s+[A-Z0-9\.-:_]+\s+(@|AT)\s*\$?\s*(\d+[\.,]?\d*)/u', $norm, $m)){
                        $qty = (float)str_replace(',', '.', str_replace('.', '', $m[2]));
                        return $qty>0 ? $qty : null;
                    }
                    return null;
                };
                $qQty = $parserQ($row->title ?? '', $row->detail ?? '');
                if ($qQty !== null) { $buyQtySum += (float)$qQty; }
            }
            if ($sellQtySum !== null) {
                $parserS = function($title, $detail){
                    $txt = trim((string)($title ?: ''));
                    if($detail){ $txt .= ' '.trim((string)$detail); }
                    $t = mb_strtoupper($txt,'UTF-8');
                    $norm = preg_replace('/\s+/', ' ', $t);
                    if(preg_match('/\b(VENDA)\b\s+DE\s+(\d+[\.,]?\d*)\s+[A-Z0-9\.-:_]+\s+A\s*\$\s*(\d+[\.,]?\d*)/u', $norm, $m)){
                        $qty = (float)str_replace(',', '.', str_replace('.', '', $m[2]));
                        return $qty>0 ? $qty : null;
                    }
                    if(preg_match('/\b(SELL)\b\s+(\d+[\.,]?\d*)\s+[A-Z0-9\.-:_]+\s+(@|AT)\s*\$?\s*(\d+[\.,]?\d*)/u', $norm, $m)){
                        $qty = (float)str_replace(',', '.', str_replace('.', '', $m[2]));
                        return $qty>0 ? $qty : null;
                    }
                    return null;
                };
                $qQtyS = $parserS($row->title ?? '', $row->detail ?? '');
                if ($qQtyS !== null) { $sellQtySum += (float)$qQtyS; }
            }

            // Resumo por Ativo (opcional): identificar símbolo via parser do título/detalhe
            if ($groupAsset) {
                // Parser com quantidade (seguindo padrão de positionsSummary)
                $parser = function($title, $detail){
                    $txt = trim((string)($title ?: ''));
                    if($detail){ $txt .= ' '.trim((string)$detail); }
                    $t = mb_strtoupper($txt,'UTF-8');
                    $norm = preg_replace('/\s+/', ' ', $t);
                    // PT
                    if(preg_match('/\b(COMPRA|VENDA)\b\s+DE\s+(\d+[\.,]?\d*)\s+([A-Z0-9\.\-:_]+)\s+A\s*\$\s*(\d+[\.,]?\d*)/u', $norm, $m)){
                        $type = ($m[1]==='COMPRA')?'buy':'sell';
                        $qty = (float)str_replace(',', '.', str_replace('.', '', $m[2]));
                        $sym = trim($m[3]);
                        return ['type'=>$type,'sym'=>$sym,'qty'=>$qty];
                    }
                    // EN
                    if(preg_match('/\b(BUY|SELL)\b\s+(\d+[\.,]?\d*)\s+([A-Z0-9\.\-:_]+)\s+(@|AT)\s*\$?\s*(\d+[\.,]?\d*)/u', $norm, $m)){
                        $type = ($m[1]==='BUY')?'buy':'sell';
                        $qty = (float)str_replace(',', '.', str_replace('.', '', $m[2]));
                        $sym = trim($m[3]);
                        return ['type'=>$type,'sym'=>$sym,'qty'=>$qty];
                    }
                    return null;
                };
                $p = $parser($row->title ?? '', $row->detail ?? '');
                if ($p && !empty($p['sym'])) {
                    $sym = strtoupper($p['sym']);
                    if (!isset($byAssetSummary[$sym])) { $byAssetSummary[$sym] = ['buy'=>0.0,'sell'=>0.0,'fee'=>0.0,'buy_qty'=>0.0,'sell_qty'=>0.0]; }
                    if ($p['type'] === 'buy') {
                        $byAssetSummary[$sym]['buy'] += abs($amount);
                        $byAssetSummary[$sym]['buy_qty'] += is_numeric($p['qty'] ?? null) ? (float)$p['qty'] : 0.0;
                    }
                    elseif ($p['type'] === 'sell') {
                        $byAssetSummary[$sym]['sell'] += abs($amount);
                        $byAssetSummary[$sym]['sell_qty'] += is_numeric($p['qty'] ?? null) ? (float)$p['qty'] : 0.0;
                    }
                    // Taxas: atribuir quando a categoria indicar fee e o texto tiver símbolo
                    if ($isFee) { $byAssetSummary[$sym]['fee'] += abs($amount); }
                }
            }
        }
        // Ordenar períodos desc
        krsort($periodSummary);
        if ($groupAsset && !empty($byAssetSummary)) { ksort($byAssetSummary, SORT_NATURAL | SORT_FLAG_CASE); }

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
            'byAssetSummary'=>$groupAsset ? $byAssetSummary : [],
            'filter_account_id'=>$accountId ? (int)$accountId : null,
            'filter_category'=>$category,
            'filter_status'=>$status,
            'filter_title'=>$filterTitle,
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
            'buySell'=>$buySell,
            'buyQtySum'=>$buyQtySum,
            'sellQtySum'=>$sellQtySum,
            'hasMeta'=>$hasMeta,
            'sumTotal'=>$sumTotal,
            'sumIn'=>$sumIn,
            'sumOut'=>$sumOut,
            'latestSnapshots'=>$latestSnapshots,
            'perPage'=>$perPage,
            'sources'=>$sources,
            'canComputeRunning'=>$canComputeRunning,
            'showRunning'=>$showRunning,
            'groupAsset'=>$groupAsset,
            'onlyBuySell'=>$onlyBuySell,
            'paginate'=>$paginate,
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

    public function exportByAssetCsv(Request $request)
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
        $direction = $request->input('direction');
        $valMin = $request->input('val_min');
        $valMax = $request->input('val_max');
        $source = $request->input('source');

        $q = InvestmentAccountCashEvent::where('user_id',$userId);
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

    $rows = $q->get(['title','detail','category','amount']);

        // Parser mínimo para identificar símbolo e tipo (buy/sell) a partir de título/detalhe
        $parser = function($title, $detail){
            $txt = trim((string)($title ?: ''));
            if($detail){ $txt .= ' '.trim((string)$detail); }
            $t = mb_strtoupper($txt,'UTF-8');
            $norm = preg_replace('/\s+/', ' ', $t);
            if(preg_match('/\b(COMPRA|VENDA)\b\s+DE\s+(\d+[\.,]?\d*)\s+([A-Z0-9\.\-:_]+)\s+A\s*\$\s*(\d+[\.,]?\d*)/u', $norm, $m)){
                $type = ($m[1]==='COMPRA')?'buy':'sell';
                $qty = (float)str_replace(',', '.', str_replace('.', '', $m[2]));
                $sym = trim($m[3]);
                return ['type'=>$type,'sym'=>$sym,'qty'=>$qty];
            }
            if(preg_match('/\b(BUY|SELL)\b\s+(\d+[\.,]?\d*)\s+([A-Z0-9\.\-:_]+)\s+(@|AT)\s*\$?\s*(\d+[\.,]?\d*)/u', $norm, $m)){
                $type = ($m[1]==='BUY')?'buy':'sell';
                $qty = (float)str_replace(',', '.', str_replace('.', '', $m[2]));
                $sym = trim($m[3]);
                return ['type'=>$type,'sym'=>$sym,'qty'=>$qty];
            }
            return null;
        };

        $byAsset = [];
        foreach($rows as $row){
            $cat = strtolower(trim((string)$row->category));
            $amount = (float)$row->amount;
            $isFee = (strpos($cat,'fee')!==false) || (strpos($cat,'taxa')!==false) || (strpos($cat,'commission')!==false) || (strpos($cat,'comissão')!==false);
            $p = $parser($row->title ?? '', $row->detail ?? '');
            if(!$p || empty($p['sym'])) continue;
            $sym = strtoupper($p['sym']);
            if (!isset($byAsset[$sym])) { $byAsset[$sym] = ['buy'=>0.0,'sell'=>0.0,'fee'=>0.0,'buy_qty'=>0.0,'sell_qty'=>0.0]; }
            if ($p['type'] === 'buy') {
                $byAsset[$sym]['buy'] += abs($amount);
                $byAsset[$sym]['buy_qty'] += is_numeric($p['qty'] ?? null) ? (float)$p['qty'] : 0.0;
            }
            elseif ($p['type'] === 'sell') {
                $byAsset[$sym]['sell'] += abs($amount);
                $byAsset[$sym]['sell_qty'] += is_numeric($p['qty'] ?? null) ? (float)$p['qty'] : 0.0;
            }
            if ($isFee) { $byAsset[$sym]['fee'] += abs($amount); }
        }
        ksort($byAsset, SORT_NATURAL | SORT_FLAG_CASE);

        // Filtrar por símbolos selecionados exatamente como na view (quando houver parâmetro, mesmo vazio)
        if ($request->has('symbols')) {
            $symbolsParam = (string)$request->query('symbols', '');
            $wanted = array_filter(array_map('trim', explode(',', $symbolsParam)), fn($s)=>$s!=='');
            $wanted = array_map(fn($s)=>strtoupper($s), $wanted);
            if (!empty($wanted)) {
                $byAsset = array_intersect_key($byAsset, array_flip($wanted));
            } else {
                // nenhum visível -> força vazio
                $byAsset = [];
            }
        }

        // Totais gerais
    $tBuy = array_sum(array_map(fn($r)=>$r['buy'] ?? 0, $byAsset));
    $tSell = array_sum(array_map(fn($r)=>$r['sell'] ?? 0, $byAsset));
        $tFee = array_sum(array_map(fn($r)=>$r['fee'] ?? 0, $byAsset));
        $tNet = $tSell - $tBuy;
        $tVarPct = $tBuy>0 ? ($tNet/$tBuy)*100.0 : null;
    $tBuyQty = array_sum(array_map(fn($r)=>$r['buy_qty'] ?? 0, $byAsset));
    $tSellQty = array_sum(array_map(fn($r)=>$r['sell_qty'] ?? 0, $byAsset));

        $filename = 'cash_by_asset_'.date('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"'
        ];
    $callback = function() use ($byAsset, $tBuy, $tSell, $tFee, $tNet, $tVarPct, $tBuyQty, $tSellQty){
            $out = fopen('php://output','w');
            // BOM UTF-8
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['symbol','buy','buy_qty','sell','sell_qty','fee','net','variation_pct'], ';');
            foreach($byAsset as $sym => $s){
                $buy = (float)($s['buy'] ?? 0);
                $sell = (float)($s['sell'] ?? 0);
                $fee = (float)($s['fee'] ?? 0);
                $net = $sell - $buy;
                $varPct = $buy>0 ? ($net/$buy)*100.0 : null;
                $bqty = (float)($s['buy_qty'] ?? 0);
                $sqty = (float)($s['sell_qty'] ?? 0);
                fputcsv($out, [
                    $sym,
                    number_format($buy,6,'.',''),
                    number_format($bqty,6,'.',''),
                    number_format($sell,6,'.',''),
                    number_format($sqty,6,'.',''),
                    number_format($fee,6,'.',''),
                    number_format($net,6,'.',''),
                    $varPct!==null ? number_format($varPct,6,'.','') : '',
                ], ';');
            }
            // Totais
            fputcsv($out, [
                'TOTAL',
                number_format($tBuy,6,'.',''),
                number_format($tBuyQty,6,'.',''),
                number_format($tSell,6,'.',''),
                number_format($tSellQty,6,'.',''),
                number_format($tFee,6,'.',''),
                number_format($tNet,6,'.',''),
                $tVarPct!==null ? number_format($tVarPct,6,'.','') : '',
            ], ';');
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

    public function destroy(Request $request, int $event)
    {
        $userId = (int)Auth::id();
        $row = InvestmentAccountCashEvent::where('user_id',$userId)->where('id',$event)->first();
        if (!$row) {
            return redirect()->back()->with('error','Evento não encontrado ou não pertence a este usuário.');
        }
        try {
            $row->delete();
        } catch (\Throwable $t) {
            return redirect()->back()->with('error','Falha ao excluir o evento.');
        }
        return redirect()->back()->with('success','Evento excluído com sucesso.');
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
                                // Persistência normalizada: sempre em YYYY-MM-DD 00:00:00 (startOfDay)
                                $today = Carbon::today()->startOfDay();
                                $existing = AssetDailyStat::where('symbol', strtoupper($sym))
                                    ->whereRaw('DATE(`date`) = ?', [$today->format('Y-m-d')])
                                    ->first();
                                if ($existing) {
                                    $existing->close_value = $price;
                                    $existing->is_accurate = true;
                                    $existing->save();
                                } else {
                                    AssetDailyStat::create([
                                        'symbol' => strtoupper($sym),
                                        'date' => $today,
                                        'close_value' => $price,
                                        'is_accurate' => true,
                                    ]);
                                }
                                // Re-leitura para garantir consistência exibida com DB
                                $row = AssetDailyStat::where('symbol', strtoupper($sym))
                                    ->whereRaw('DATE(`date`) = ?', [$today->format('Y-m-d')])
                                    ->orderBy('date', 'desc')->first();
                                if ($row && is_numeric($row->close_value)) {
                                    $price = (float) $row->close_value;
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
                            // Persistência normalizada: sempre em YYYY-MM-DD 00:00:00 (startOfDay)
                            $today = Carbon::today()->startOfDay();
                            $existing = AssetDailyStat::where('symbol', strtoupper($sym))
                                ->whereRaw('DATE(`date`) = ?', [$today->format('Y-m-d')])
                                ->first();
                            if ($existing) {
                                $existing->close_value = $price;
                                $existing->is_accurate = true;
                                $existing->save();
                            } else {
                                AssetDailyStat::create([
                                    'symbol' => strtoupper($sym),
                                    'date' => $today,
                                    'close_value' => $price,
                                    'is_accurate' => true,
                                ]);
                            }
                            // Re-leitura para garantir consistência exibida com DB
                            $row = AssetDailyStat::where('symbol', strtoupper($sym))
                                ->whereRaw('DATE(`date`) = ?', [$today->format('Y-m-d')])
                                ->orderBy('date', 'desc')->first();
                            if ($row && is_numeric($row->close_value)) {
                                $price = (float) $row->close_value;
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
