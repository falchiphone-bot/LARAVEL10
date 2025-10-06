<?php
namespace App\Http\Controllers;

use App\Models\UserHolding;
use App\Models\AssetVariation;
use App\Services\MarketDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Models\InvestmentAccountCashEvent;

class PortfolioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $userId = Auth::id();
        $missingTable = false;
        $codeFilter = strtoupper(trim((string)$request->input('code')));
        $accountFilter = $request->input('account_id');
    $accountNameFilter = trim((string)$request->input('account'));
        $sort = (string)$request->input('sort','');
        $dir = strtolower((string)$request->input('dir','asc')) === 'desc' ? 'desc' : 'asc';
        if(!Schema::hasTable('user_holdings')){
            $missingTable = true;
            $holdings = collect();
        } else {
            $q = UserHolding::with('account')->where('user_id', $userId);
            if($codeFilter !== ''){
                // filtro prefixo (mais eficiente). Se quiser substring, trocar por LIKE "%$codeFilter%".
                $q->where('code','LIKE', $codeFilter.'%');
            }
            if(is_numeric($accountFilter)){
                $q->where('account_id', (int)$accountFilter);
            }
            if($accountNameFilter !== ''){
                $like = '%'.str_replace(['%','_'],['\%','\_'],$accountNameFilter).'%';
                $q->whereHas('account', function($sub) use ($like){
                    $sub->where('account_name','LIKE',$like)
                        ->orWhere('broker','LIKE',$like);
                });
            }
            $holdings = $q->orderBy('code')->get();
        }

        // Atualização opcional de preços (lazy) quando faltando current_price
        $updateQuotes = $request->boolean('refresh');
        $md = $updateQuotes ? app(MarketDataService::class) : null;
        $updatedCodes = [];
    if(!$missingTable && $updateQuotes && $holdings->count() < 300){
            foreach ($holdings as $h){
                if(!$h->current_price || $request->boolean('force')){
                    $q = $md->getQuote($h->code);
                    if($q['price'] !== null){
                        $h->current_price = $q['price'];
                        $h->currency = $q['currency'] ?: $h->currency;
                        $h->save();
                        $updatedCodes[] = $h->code;
                    }
                }
            }
        }

        // Mapear última variação mensal por código (ano/mês máximo)
        $variationMap = [];
    if(!$missingTable && $holdings->isNotEmpty()){
            $codes = $holdings->pluck('code')->unique()->values();
            $rows = AssetVariation::select('asset_code','year','month','variation','chat_id')
                ->whereIn('asset_code', $codes)
                ->orderBy('year','desc')->orderBy('month','desc')
                ->get();
            foreach($rows as $r){
                $ck = strtoupper($r->asset_code);
                if(!isset($variationMap[$ck])){
                    $variationMap[$ck] = [
                        'year' => $r->year,
                        'month' => $r->month,
                        'variation' => (float)$r->variation,
                        'chat_id' => $r->chat_id,
                    ];
                }
            }
        }

        // Montar métricas
        $totalInvested = 0.0; $totalCurrent = 0.0; $rowsOut = [];
        $holdingsCount = $holdings->count();
        $enableCross = $holdingsCount > 0 && $holdingsCount <= 150; // evitar N+1 pesado em carteiras grandes
        foreach ($holdings as $h){
            $inv = (float)$h->invested_value;
            $mktPrice = $h->current_price ?: null;
            $curVal = ($mktPrice !== null) ? $mktPrice * (float)$h->quantity : null;
            $totalInvested += $inv;
            if($curVal !== null) $totalCurrent += $curVal;
            $var = $variationMap[strtoupper($h->code)] ?? null;
            // Cálculo aproximado de cobertura de caixa: soma dos fluxos de compra/venda relacionados ao código na conta.
            $cashCoverPct = null; $approxAcquiredQty = null; $remainingQty = null; $buyAmount = 0.0; $sellAmount = 0.0; $tradeEvents = 0;
            if($enableCross && $h->quantity > 0){
                try {
                    $symbol = strtoupper($h->code);
                    $events = InvestmentAccountCashEvent::query()
                        ->where('user_id',$userId)
                        ->where('account_id',$h->account_id)
                        ->where(function($w) use($symbol){
                            $w->where('title','LIKE','%'.$symbol.'%');
                        })
                        ->where(function($w){
                            $w->where('title','LIKE','%compra%')
                              ->orWhere('title','LIKE','%venda%')
                              ->orWhere('title','LIKE','%buy%')
                              ->orWhere('title','LIKE','%sell%');
                        })
                        ->orderBy('event_date','desc')
                        ->limit(400)
                        ->get(['title','amount']);
                    foreach($events as $ev){
                        $t = mb_strtolower($ev->title);
                        $amt = (float)$ev->amount;
                        if(str_contains($t,'compra') || str_contains($t,'buy')){
                            // Compra normalmente é saída de caixa (valor negativo). Normalizar como valor absoluto acumulado.
                            $buyAmount += abs($amt);
                            $tradeEvents++;
                        } elseif(str_contains($t,'venda') || str_contains($t,'sell')){
                            // Venda é entrada de caixa (valor positivo)
                            $sellAmount += abs($amt);
                            $tradeEvents++;
                        }
                    }
                    if($buyAmount > 0 && $h->avg_price > 0){
                        // Aproxima quantidade adquirida bruta = total gasto em compras / preço médio atual (heurística)
                        $approxAcquiredQty = $buyAmount / $h->avg_price;
                        // Ajustar por vendas: reduzir quantidade equivalente vendida usando preço médio.
                        if($sellAmount > 0){
                            $approxAcquiredQty -= ($sellAmount / $h->avg_price);
                        }
                        if($approxAcquiredQty < $h->quantity){
                            // Se a aproximação for menor que a atual, assume ao menos o atual para evitar >100% depois
                            $approxAcquiredQty = $h->quantity;
                        }
                        $cashCoverPct = $approxAcquiredQty > 0 ? min(100.0, ($h->quantity / $approxAcquiredQty)*100.0) : null;
                        $remainingQty = $approxAcquiredQty - $h->quantity;
                    }
                } catch(\Throwable $e){
                    // Silencioso: não compromete a página
                }
            }
            $rowsOut[] = [
                'id' => $h->id,
                'account_id' => $h->account_id,
                'code' => $h->code,
                'account' => $h->account?->account_name,
                'broker' => $h->account?->broker,
                'quantity' => (float)$h->quantity,
                'avg_price' => (float)$h->avg_price,
                'invested_value' => $inv,
                'current_price' => $mktPrice,
                'current_value' => $curVal,
                'gain_loss_abs' => ($curVal !== null) ? ($curVal - $inv) : null,
                'gain_loss_pct' => ($curVal !== null && $inv>0.0) ? (($curVal/$inv)-1.0)*100.0 : null,
                'variation_monthly' => $var['variation'] ?? null,
                'variation_period' => $var ? sprintf('%04d-%02d',$var['year'],$var['month']) : null,
                'cash_cover_pct' => $cashCoverPct,
                'cash_cover_approx_acquired_qty' => $approxAcquiredQty,
                'cash_cover_remaining_qty' => $remainingQty,
                'cash_cover_trade_events' => $tradeEvents,
            ];
        }

        // Ordenação em memória (colunas derivadas). Campos suportados
        $sortable = [
            'code','account','quantity','avg_price','invested_value','current_price','current_value','gain_loss_abs','gain_loss_pct','variation_monthly'
        ];
        if($sort && in_array($sort, $sortable, true)){
            usort($rowsOut, function($a,$b) use ($sort,$dir){
                $av = $a[$sort]; $bv = $b[$sort];
                // Nulls sempre no fim independentemente da direção
                $aNull = is_null($av); $bNull = is_null($bv);
                if($aNull && $bNull) return 0;
                if($aNull) return 1; // a depois
                if($bNull) return -1; // b depois
                if($av == $bv) return 0;
                if($dir === 'asc') return ($av < $bv) ? -1 : 1;
                return ($av > $bv) ? -1 : 1;
            });
        } else {
            // default já ordenado por code na query; manter
        }

        // Totais e agregados
        $agg = [
            'total_invested' => $totalInvested,
            'total_current' => $totalCurrent,
            'total_gain_loss_abs' => $totalCurrent ? ($totalCurrent - $totalInvested) : null,
            'total_gain_loss_pct' => ($totalCurrent>0 && $totalInvested>0) ? (($totalCurrent/$totalInvested)-1.0)*100.0 : null,
        ];

        return view('portfolio.index', [
            'rows' => $rowsOut,
            'agg' => $agg,
            'updatedCodes' => $updatedCodes,
            'refresh' => $updateQuotes,
            'missingTable' => $missingTable,
            'filter_code' => $codeFilter,
            'filter_account_id' => is_numeric($accountFilter) ? (int)$accountFilter : null,
            'filter_account_name' => $accountNameFilter,
            'filter_accounts' => \App\Models\InvestmentAccount::where('user_id',$userId)->orderBy('account_name')->get(['id','account_name','broker']),
            'sort' => $sort,
            'dir' => $dir,
            'cross_cash_enabled' => $enableCross,
        ]);
    }
}
