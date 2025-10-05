<?php
namespace App\Http\Controllers;

use App\Models\UserHolding;
use App\Models\AssetVariation;
use App\Services\MarketDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

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
    foreach ($holdings as $h){
            $inv = (float)$h->invested_value;
            $mktPrice = $h->current_price ?: null;
            $curVal = ($mktPrice !== null) ? $mktPrice * (float)$h->quantity : null;
            $totalInvested += $inv;
            if($curVal !== null) $totalCurrent += $curVal;
            $var = $variationMap[strtoupper($h->code)] ?? null;
            $rowsOut[] = [
                'id' => $h->id,
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
            ];
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
            'filter_accounts' => \App\Models\InvestmentAccount::where('user_id',$userId)->orderBy('account_name')->get(['id','account_name','broker']),
        ]);
    }
}
