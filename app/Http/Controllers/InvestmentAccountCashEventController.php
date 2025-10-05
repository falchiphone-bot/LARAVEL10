<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\InvestmentAccountCashEvent;
use App\Models\InvestmentAccountCashSnapshot;
use App\Models\InvestmentAccount;

class InvestmentAccountCashEventController extends Controller
{
    public function index(Request $request)
    {
        $userId = (int)Auth::id();
        $accountId = $request->input('account_id');
        $category = $request->input('category');
        $status = trim((string)$request->input('status'));
        $from = $request->input('from');
        $to = $request->input('to');
        $perPage = min(200, max(10, (int)$request->input('per_page', 50)));

        $q = InvestmentAccountCashEvent::with('account')
            ->where('user_id',$userId);
        if($accountId){ $q->where('account_id',$accountId); }
        if($category){ $q->where('category',$category); }
        if($status!==''){ $q->where('status','LIKE','%'.$status.'%'); }
        if($from){ $q->whereDate('event_date','>=',$from); }
        if($to){ $q->whereDate('event_date','<=',$to); }

        $q->orderBy('event_date','desc')->orderBy('id','desc');
        $events = $q->paginate($perPage)->appends($request->query());

        // Totais filtrados (sem paginação)
        $aggregateQuery = clone $q; // clone após filtros (remove orderings para sum)
        $allFiltered = $aggregateQuery->get(['amount']);
        $sumTotal = (float)$allFiltered->sum('amount');
        $sumIn = (float)$allFiltered->filter(fn($e)=>$e->amount>0)->sum('amount');
        $sumOut = (float)$allFiltered->filter(fn($e)=>$e->amount<0)->sum('amount');

        $accounts = InvestmentAccount::where('user_id',$userId)->orderBy('account_name')->get();
        $categories = InvestmentAccountCashEvent::where('user_id',$userId)->distinct()->pluck('category')->sort()->values();

        // Snapshot mais recente por conta (para eventual exibição rápida)
        $latestSnapshots = InvestmentAccountCashSnapshot::where('user_id',$userId)
            ->selectRaw('account_id, MAX(snapshot_at) as snapshot_at')
            ->groupBy('account_id')
            ->pluck('snapshot_at','account_id');

        return view('portfolio.cash_events_index', [
            'events'=>$events,
            'accounts'=>$accounts,
            'categories'=>$categories,
            'filter_account_id'=>$accountId ? (int)$accountId : null,
            'filter_category'=>$category,
            'filter_status'=>$status,
            'filter_from'=>$from,
            'filter_to'=>$to,
            'sumTotal'=>$sumTotal,
            'sumIn'=>$sumIn,
            'sumOut'=>$sumOut,
            'latestSnapshots'=>$latestSnapshots,
            'perPage'=>$perPage,
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
}
