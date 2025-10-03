<?php

namespace App\Http\Controllers;

use App\Models\InvestmentDailyBalance;
use App\Models\OpenAIChatRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class InvestmentDailyBalanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:INVESTIMENTOS SNAPSHOTS - LISTAR')->only(['index']);
        $this->middleware('permission:INVESTIMENTOS SNAPSHOTS - CRIAR')->only(['store']);
        $this->middleware('permission:INVESTIMENTOS SNAPSHOTS - EXCLUIR')->only(['destroy']);
        $this->middleware('permission:INVESTIMENTOS SNAPSHOTS - EXPORTAR')->only(['exportCsv']);
        $this->middleware('permission:INVESTIMENTOS SNAPSHOTS - RESTAURAR')->only(['restore']);
    }

    /**
     * Lista snapshots de saldo diário ordenados por data desc e calcula diferença/variação.
     */
    public function index(Request $request)
    {
        $userId = (int) Auth::id();
        $withDeleted = $request->boolean('with_deleted');
        $latestPerDay = $request->boolean('latest_per_day');
        $baseMode = $request->input('base_mode','oldest'); // oldest | recent
        $compact = $request->boolean('compact');
        $withSpark = $request->boolean('spark');
        $range = $request->input('range'); // 7d | 30d | ytd
        $from = trim((string)$request->input('from'));
        $to = trim((string)$request->input('to'));
        if(($from === '' && $to === '') && in_array($range, ['7d','30d','ytd'], true)) {
            $today = now()->toDateString();
            if($range==='7d') { $from = now()->subDays(6)->toDateString(); $to=$today; }
            elseif($range==='30d') { $from = now()->subDays(29)->toDateString(); $to=$today; }
            elseif($range==='ytd') { $from = now()->startOfYear()->toDateString(); $to=$today; }
        }
        $perPage = (int) $request->input('per_page', 50); if($perPage<10) $perPage=10; if($perPage>200) $perPage=200;
        $page = max(1, (int)$request->input('page', 1));
        $query = InvestmentDailyBalance::where('user_id', $userId);
        if ($withDeleted) { $query->withTrashed(); }
        if ($from !== '') {
            // validar formato simples YYYY-MM-DD
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/',$from)) {
                $query->whereDate('snapshot_at','>=',$from);
            }
        }
        if ($to !== '') {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/',$to)) {
                $query->whereDate('snapshot_at','<=',$to);
            }
        }
        if ($latestPerDay) {
            // Trazer mais registros para deduplicar dias (cap em 5000)
            $raw = $query->orderByDesc('snapshot_at')->limit(5000)->get();
            $seenDays = []; $picked = [];
            foreach ($raw as $b) {
                $d = optional($b->snapshot_at)->toDateString();
                if (!isset($seenDays[$d])) { $seenDays[$d]=true; $picked[] = $b; }
            }
            $totalItems = count($picked);
            $totalPages = (int) ceil($totalItems / $perPage);
            $slice = array_slice($picked, ($page-1)*$perPage, $perPage);
            $balances = collect($slice);
        } else {
            // Paginação via offset/limit (para não usar paginate por custom coleções posteriormente)
            $baseQuery = clone $query;
            $totalItems = (clone $baseQuery)->count();
            $totalPages = (int) ceil($totalItems / $perPage);
            $balances = $query->orderByDesc('snapshot_at')
                ->skip(($page-1)*$perPage)
                ->take($perPage)
                ->get();
        }
        $rows = [];
    $last = $balances->last();
    $first = $balances->first();
    $accBaseVal = ($baseMode === 'recent') ? ($first?->total_amount) : ($last?->total_amount);
        $count = $balances->count();
        for ($i=0; $i<$count; $i++) {
            $cur = $balances[$i];
            $diff = null; $var = null;
            if ($i+1 < $count) {
                $older = $balances[$i+1];
                $diff = (float)$cur->total_amount - (float)$older->total_amount; // positivo=crescimento
                $olderBase = (float)$older->total_amount;
                if (abs($olderBase) > 1e-10) {
                    $var = ($diff / $olderBase) * 100.0; // var % sobre valor mais antigo
                }
            }
            $accDiff = null; $accPerc = null;
            if ($accBaseVal !== null) {
                $accDiff = (float)$cur->total_amount - (float)$accBaseVal;
                if (abs((float)$accBaseVal) > 1e-10) { $accPerc = ($accDiff / (float)$accBaseVal) * 100.0; }
            }
            $rows[] = [
                'model'=>$cur,
                'diff'=>$diff,
                'var'=>$var,
                'perc'=>$var,
                'prev_total'=>null,
                'acc_diff'=>$accDiff,
                'acc_perc'=>$accPerc,
            ];
        }
        // Série para sparkline (mais antigo -> mais recente)
        $sparkSeries = $withSpark ? $balances->pluck('total_amount')->reverse()->values()->all() : [];

        return view('investments.daily_balances.index', [
            'rows' => $rows,
            'withDeleted' => $withDeleted,
            'latestPerDay' => $latestPerDay,
            'baseMode' => $baseMode,
            'compact' => $compact,
            'sparkSeries' => $sparkSeries,
            'from' => $from,
            'to' => $to,
            'range' => $range,
            'page' => $page,
            'perPage' => $perPage,
            'totalItems' => $totalItems ?? count($rows),
            'totalPages' => $totalPages ?? 1,
        ]);
    }

    /**
     * Gera e salva um novo snapshot: soma o último amount de cada ativo do usuário.
     */
    public function store(Request $request)
    {
        $userId = (int) Auth::id();
        // Novo cálculo: usa a mesma lógica da tela de investimentos (soma total_invested de investment_accounts com filtros opcionais)
        $from = $request->input('from');
        $to = $request->input('to');
        $account = trim((string)$request->input('account'));
        $broker = trim((string)$request->input('broker'));

        $q = DB::table('investment_accounts')->where('user_id', $userId);
        if (!empty($from)) { $q->whereDate('date', '>=', $from); }
        if (!empty($to)) { $q->whereDate('date', '<=', $to); }
        if ($account !== '') { $q->where('account_name', 'LIKE', '%'.$account.'%'); }
        if ($broker !== '') { $q->where('broker', 'LIKE', '%'.$broker.'%'); }
        $total = (float)$q->sum('total_invested');
        $now = now();
        $driver = DB::getDriverName();
        if ($driver === 'sqlsrv') {
            // Inserção manual totalmente parametrizada (sem DB::raw) para evitar perda de aspas no datetime
            // Formato com fração 7 dígitos compatível com DATETIME2(7)
            $stamp = $now->copy()->timezone('UTC')->format('Y-m-d H:i:s.u');
            // Carbon gera 6 dígitos em microseconds; acrescentar 0 final para 7 dígitos
            if (preg_match('/^(.*\.\d{6})$/', $stamp)) {
                $stamp .= '0';
            }
            $id = DB::table('investment_daily_balances')->insertGetId([
                'user_id' => $userId,
                'snapshot_at' => $stamp,
                'total_amount' => $total,
                'created_at' => $stamp,
                'updated_at' => $stamp,
            ]);
            // (nenhum cálculo aqui; lógica de evolução é só para listagem/export)
        } else {
            $snapshot = InvestmentDailyBalance::create([
                'user_id' => $userId,
                'snapshot_at' => $now,
                'total_amount' => $total,
            ]);
        }
        if ($request->wantsJson()) {
            return response()->json([
                'ok'=>true,
                'id'=>$snapshot->id,
                'total'=>$snapshot->total_amount,
                'filters'=>array_filter([
                    'from'=>$from?:null,
                    'to'=>$to?:null,
                    'account'=>$account?:null,
                    'broker'=>$broker?:null,
                ])
            ]);
        }
        // Redirecionar mantendo filtros para coerência
        return redirect()->route('investments.daily-balances.index', array_filter([
            'from'=>$from?:null,
            'to'=>$to?:null,
            'account'=>$account?:null,
            'broker'=>$broker?:null,
        ]))->with('success','Snapshot salvo.');
    }

    /**
     * Exporta o histórico em CSV (ordenado desc por snapshot_at).
     */
    public function exportCsv(Request $request)
    {
        $userId = (int) Auth::id();
        $withDeleted = $request->boolean('with_deleted');
    $latestPerDay = $request->boolean('latest_per_day');
    $baseMode = $request->input('base_mode','oldest');
        $from = trim((string)$request->input('from'));
        $to = trim((string)$request->input('to'));
        $q = InvestmentDailyBalance::where('user_id',$userId);
        if ($withDeleted) { $q->withTrashed(); }
        if ($from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/',$from)) { $q->whereDate('snapshot_at','>=',$from); }
        if ($to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/',$to)) { $q->whereDate('snapshot_at','<=',$to); }
        if ($latestPerDay) {
            $raw = $q->orderByDesc('snapshot_at')->limit(5000)->get(['snapshot_at','total_amount','deleted_at']);
            $seen = []; $selected = [];
            foreach ($raw as $b) {
                $d = optional($b->snapshot_at)->toDateString();
                if (!isset($seen[$d])) { $seen[$d]=true; $selected[] = $b; }
            }
            $balances = collect($selected);
        } else {
            $balances = $q->orderByDesc('snapshot_at')
                ->get(['snapshot_at','total_amount','deleted_at']);
        }
    // Pré-calcular métricas igual à tela (diferença atribuída à linha mais recente usando a próxima mais antiga; acumulado baseado no snapshot mais antigo)
        $rows = [];
    $last = $balances->last();
    $first = $balances->first();
    $accBaseVal = ($baseMode === 'recent') ? ($first?->total_amount) : ($last?->total_amount);
        foreach ($balances as $b) {
            $rows[] = [
                'snapshot_at' => optional($b->snapshot_at)->format('Y-m-d H:i:s'),
                'total_amount' => (float)$b->total_amount,
                'deleted_at' => $b->deleted_at?->format('Y-m-d H:i:s'),
                'diff' => null,
                'var' => null,
                'acc_diff' => null,
                'acc_perc' => null,
            ];
        }
        $count = count($rows);
        for ($i=0; $i<$count; $i++) {
            $curVal = $rows[$i]['total_amount'];
            // acumulado
            if ($accBaseVal !== null) {
                $accDiff = $curVal - (float)$accBaseVal;
                $accPerc = (abs((float)$accBaseVal) > 1e-10) ? ($accDiff / (float)$accBaseVal) * 100.0 : null;
                $rows[$i]['acc_diff'] = $accDiff;
                $rows[$i]['acc_perc'] = $accPerc;
            }
            if ($i+1 < $count) {
                $olderVal = $rows[$i+1]['total_amount'];
                $diff = $curVal - $olderVal; // positivo = crescimento
                $var = abs($olderVal) > 1e-10 ? ($diff / $olderVal) * 100.0 : null; // base = mais antigo
                $rows[$i]['diff'] = $diff;
                $rows[$i]['var'] = $var;
            }
        }
        $callback = function() use ($rows){
            $out = fopen('php://output','w');
            fputcsv($out, ['SnapshotAt','TotalAmount','Diff','VarPerc','AccDiff','AccPerc','DeletedAt'], ';');
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['snapshot_at'],
                    number_format($r['total_amount'], 6, '.', ''),
                    $r['diff'] === null ? '' : number_format($r['diff'], 6, '.', ''),
                    $r['var'] === null ? '' : number_format($r['var'], 6, '.', ''),
                    $r['acc_diff'] === null ? '' : number_format($r['acc_diff'], 6, '.', ''),
                    $r['acc_perc'] === null ? '' : number_format($r['acc_perc'], 6, '.', ''),
                    $r['deleted_at']
                ], ';');
            }
            fclose($out);
        };
        $file = 'daily_balances_'.date('Ymd_His').'.csv';
        return response()->streamDownload($callback,$file,[ 'Content-Type'=>'text/csv; charset=UTF-8']);
    }

    /**
     * Exclui um snapshot específico do usuário autenticado.
     */
    public function destroy(InvestmentDailyBalance $dailyBalance)
    {
        if ((int)$dailyBalance->user_id !== (int)Auth::id()) {
            abort(403);
        }
        $dailyBalance->delete();
        if (request()->wantsJson()) {
            return response()->json(['ok'=>true]);
        }
        return back()->with('success','Snapshot excluído.');
    }

        public function restore($id)
        {
            $userId = (int) Auth::id();
            $balance = InvestmentDailyBalance::withTrashed()->where('user_id', $userId)->findOrFail($id);
            if (!$balance->trashed()) {
                return redirect()->back()->with('status', 'Snapshot já ativo.');
            }
            $balance->restore();
            return redirect()->back()->with('status', 'Snapshot restaurado.');
        }
}
