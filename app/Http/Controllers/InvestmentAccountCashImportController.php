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

    // Novo: importação via CSV avenue-report-statement
    public function formCsv()
    {
        $accounts = InvestmentAccount::where('user_id',Auth::id())->orderBy('account_name')->get();
        return view('portfolio.cash_import_csv',[ 'accounts'=>$accounts ]);
    }

    public function storeCsv(Request $request, InvestmentCashImportService $svc)
    {
        $request->validate([
            'account_id'=>'required|integer|exists:investment_accounts,id',
            'csv_file'=>'required|file|mimes:csv,txt'
        ]);
        $userId = (int)Auth::id();
        $accountId = (int)$request->input('account_id');
        $content = file_get_contents($request->file('csv_file')->getRealPath());
        $parsed = $svc->parseAvenueStatementCsv($content);
        $snapshotSaved = null; $eventsInserted=0; $eventsSkipped=0; $eventsUpdated=0;
        DB::transaction(function() use ($parsed,$userId,$accountId,&$snapshotSaved,&$eventsInserted,&$eventsSkipped,&$eventsUpdated){
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
                // Critério de merge sem external id: mesma data(s), título e valor
                $found = InvestmentAccountCashEvent::where('user_id',$userId)
                    ->where('account_id',$accountId)
                    ->where('event_date',$e['event_date'])
                    ->where(function($q) use ($e){
                        if($e['settlement_date']){ $q->where('settlement_date',$e['settlement_date']); } else { $q->whereNull('settlement_date'); }
                    })
                    ->where('title',$e['title'])
                    ->where('amount',$e['amount'])
                    ->first();
                if($found){
                    // Atualizar categoria se mudou ou status (mantendo imutabilidade de amount)
                    $dirty = false;
                    if($found->category !== $e['category']){ $found->category = $e['category']; $dirty = true; }
                    if($e['status'] && $found->status !== $e['status']){ $found->status = $e['status']; $dirty = true; }
                    if($dirty){ $found->save(); $eventsUpdated++; } else { $eventsSkipped++; }
                    continue;
                }
                $gh = sha1(json_encode([$userId,$accountId,$e['event_date'],$e['settlement_date'],$e['title'],$e['detail'],$e['amount'],$e['status'],'avenue_csv']));
                InvestmentAccountCashEvent::create(array_merge($e,[
                    'user_id'=>$userId,
                    'account_id'=>$accountId,
                    'group_hash'=>$gh
                ]));
                $eventsInserted++;
            }
        });
        $msg = 'CSV caixa: '.($snapshotSaved?'snapshot OK':'sem snapshot').' | inseridos='.$eventsInserted.' | atualizados='.$eventsUpdated.' | ignorados='.$eventsSkipped.' | erros='.count($parsed['errors']);
        return redirect()->to(route('cash.events.index').'#gsc.tab=0')->with('success',$msg);
    }
}
