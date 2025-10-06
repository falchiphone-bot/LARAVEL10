<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\InvestmentAccountCashEvent;
use App\Models\InvestmentAccountCashSnapshot;
use App\Models\InvestmentAccount;
use Illuminate\Support\Facades\DB;

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
}
