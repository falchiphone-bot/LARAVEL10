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
        $this->middleware(['permission:OPENAI - CHAT']); // Reutiliza mesma permissão existente
    }

    /**
     * Lista snapshots de saldo diário ordenados por data desc e calcula diferença/variação.
     */
    public function index(Request $request)
    {
        $userId = (int) Auth::id();
        $withDeleted = $request->boolean('with_deleted');
        $query = InvestmentDailyBalance::where('user_id', $userId);
        if ($withDeleted) { $query->withTrashed(); }
        $balances = $query->orderByDesc('snapshot_at')
            ->limit(200)
            ->get();
        // Calcular métricas de evolução
        $rows = [];
        $prev = null;
        foreach ($balances as $b) {
            $diff = null; $var = null;
            if ($prev) {
                $diff = (float)$b->total_amount - (float)$prev->total_amount;
                if (abs((float)$prev->total_amount) > 0.0000001) {
                    $var = ($diff / (float)$prev->total_amount) * 100.0;
                }
            }
            $rows[] = [
                'model' => $b,
                'diff' => $diff,
                'var' => $var,
                'perc' => $var, // alias para compatibilidade com view existente
                'prev_total' => $prev?->total_amount,
            ];
            $prev = $b;
        }
        return view('investments.daily_balances.index', [
            'rows' => $rows,
            'withDeleted' => $withDeleted,
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
        $total = (float) ($q->sum('total_invested'));
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
            $snapshot = InvestmentDailyBalance::find($id);
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
        $q = InvestmentDailyBalance::where('user_id',$userId);
        if ($withDeleted) { $q->withTrashed(); }
        $balances = $q->orderByDesc('snapshot_at')
            ->get(['snapshot_at','total_amount','deleted_at']);
        $callback = function() use ($balances){
            $out = fopen('php://output','w');
            fputcsv($out, ['SnapshotAt','TotalAmount','DeletedAt'], ';');
            foreach ($balances as $b) {
                fputcsv($out, [
                    optional($b->snapshot_at)->format('Y-m-d H:i:s'),
                    number_format((float)$b->total_amount, 6, '.', ''),
                    $b->deleted_at?->format('Y-m-d H:i:s')
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
