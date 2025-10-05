<?php
namespace App\Http\Controllers;

use App\Services\InvestmentCashImportService;
use App\Models\InvestmentAccountCashSnapshot;
use App\Models\InvestmentAccountCashEvent;
use App\Models\InvestmentAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvestmentAccountCashImportController extends Controller
{
    public function form()
    {
        $accounts = InvestmentAccount::where('user_id',Auth::id())->orderBy('account_name')->get();
        return view('portfolio.cash_import_screen',[ 'accounts'=>$accounts ]);
    }

    public function store(Request $request, InvestmentCashImportService $svc)
    {
        $request->validate([
            'account_id'=>'required|integer|exists:investment_accounts,id',
            'cash_raw'=>'required|string'
        ]);
        $userId = (int)Auth::id();
        $accountId = (int)$request->input('account_id');
        $parsed = $svc->parse($request->input('cash_raw'));
        $snapshotSaved = null; $eventsInserted=0; $eventsSkipped=0;
        DB::transaction(function() use ($parsed,$userId,$accountId,&$snapshotSaved,&$eventsInserted,&$eventsSkipped){
            if($parsed['snapshot']){
                $h = sha1(json_encode([$userId,$accountId,$parsed['snapshot']['available_amount'],$parsed['snapshot']['future_amount'],$parsed['snapshot']['future_date']]));
                $exists = InvestmentAccountCashSnapshot::where('raw_hash',$h)->where('user_id',$userId)->where('account_id',$accountId)->first();
                if(!$exists){
                    $snapshotSaved = InvestmentAccountCashSnapshot::create([
                        'user_id'=>$userId,
                        'account_id'=>$accountId,
                        'snapshot_at'=>now(),
                        'available_amount'=>$parsed['snapshot']['available_amount'],
                        'future_amount'=>$parsed['snapshot']['future_amount'],
                        'future_date'=>$parsed['snapshot']['future_date'],
                        'raw_hash'=>$h,
                    ]);
                } else { $snapshotSaved = $exists; }
            }
            foreach($parsed['events'] as $e){
                $gh = sha1(json_encode([$userId,$accountId,$e['event_date'],$e['settlement_date'],$e['title'],$e['detail'],$e['amount'],$e['status']]));
                $found = InvestmentAccountCashEvent::where('group_hash',$gh)->where('user_id',$userId)->where('account_id',$accountId)->first();
                if($found){ $eventsSkipped++; continue; }
                InvestmentAccountCashEvent::create(array_merge($e,[
                    'user_id'=>$userId,
                    'account_id'=>$accountId,
                    'group_hash'=>$gh
                ]));
                $eventsInserted++;
            }
        });
        $msg = 'Importação caixa: '.($snapshotSaved?'snapshot OK':'sem snapshot').' | eventos inseridos='.$eventsInserted.' | ignorados='.$eventsSkipped;
        if($request->boolean('stay')){
            return redirect()->route('cash.import.form')->with('success',$msg);
        }
        return redirect()->to(route('cash.events.index').'#gsc.tab=0')->with('success',$msg);
    }
}
