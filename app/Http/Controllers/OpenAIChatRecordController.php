<?php

namespace App\Http\Controllers;

use App\Models\OpenAIChat;
use App\Models\OpenAIChatRecord;
use App\Models\InvestmentAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use Illuminate\Database\QueryException;

class OpenAIChatRecordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    $this->middleware(['permission:OPENAI - CHAT'])->only('index','store','destroy','edit','update','assets','applyQuote','createFromQuote');
    }

    public function index(Request $request): View
    {
    $chatId = (int) $request->input('chat_id');
    $from = $request->input('from');
    $to = $request->input('to');
    $datesReapplied = false;
        // modo de variação (sequencial ou acumulada)
        $incomingMode = $request->input('var_mode');
        if($incomingMode && in_array($incomingMode, ['seq','acum'])){
            session(['openai_records_var_mode' => $incomingMode]);
        }
        $varMode = session('openai_records_var_mode', 'seq');
        $remember = $request->boolean('remember');
        $clearSaved = $request->boolean('clear_saved');
        if($clearSaved){
            session()->forget('openai_records_saved_filters');
            session()->forget('openai_records_last_from');
            session()->forget('openai_records_last_to');
        }
        if($remember){
            session(['openai_records_saved_filters' => [
                'chat_id' => $chatId ?: null,
                'from' => $from ?: null,
                'to' => $to ?: null,
            ]]);
        }
        if(!$clearSaved && !$request->hasAny(['chat_id','from','to']) && session()->has('openai_records_saved_filters')){
            $saved = session('openai_records_saved_filters');
            if($saved){
                $chatId = (int)($saved['chat_id'] ?? 0);
                $from = $saved['from'] ?? null;
                $to = $saved['to'] ?? null;
            }
        }

        // Persistência automática das datas: se vierem na requisição, salva; se não, usa últimas da sessão
        if ($request->filled('from')) {
            session(['openai_records_last_from' => $from]);
        } else {
            if (!$from) {
                $fallbackFrom = session('openai_records_last_from');
                if ($fallbackFrom) { $from = $fallbackFrom; $datesReapplied = true; }
            }
        }
        if ($request->filled('to')) {
            session(['openai_records_last_to' => $to]);
        } else {
            if (!$to) {
                $fallbackTo = session('openai_records_last_to');
                if ($fallbackTo) { $to = $fallbackTo; $datesReapplied = true; }
            }
        }

        $sort = $request->input('sort','occurred_at');
        $dir = strtolower($request->input('dir','desc')) === 'asc' ? 'asc' : 'desc';
        $invAccId = $request->input('investment_account_id'); // '' | '0' (sem) | id
        $query = OpenAIChatRecord::with(['chat:id,title,code','user:id,name','investmentAccount:id,account_name,broker']);
        $showAll = (bool)$request->input('all');

        if ($chatId > 0) {
            $query->where('chat_id', $chatId);
        }

        // Filtros de data com bindings seguros (evita formato inválido para SQL Server)
        $dateExpr = 'occurred_at';
        $driver = DB::getDriverName();
        if ($driver === 'sqlsrv') {
            try {
                $colInfo = DB::selectOne("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='openai_chat_records' AND COLUMN_NAME='occurred_at'");
                $dataType = $colInfo->DATA_TYPE ?? null;
                if ($dataType && !in_array(strtolower($dataType), ['datetime','datetime2','smalldatetime','date'])) {
                    $dateExpr = "TRY_CONVERT(datetime2, occurred_at, 103)";
                }
            } catch (\Exception $e) { /* ignora */ }
        }

        try {
            $fromDate = null; $toDate = null;
            if ($from) {
                $fromDate = preg_match('/^\d{2}\/\d{2}\/\d{4}$/',$from)
                    ? Carbon::createFromFormat('d/m/Y', $from)->startOfDay()
                    : Carbon::parse($from)->startOfDay();
            }
            if ($to) {
                $toDate = preg_match('/^\d{2}\/\d{2}\/\d{4}$/',$to)
                    ? Carbon::createFromFormat('d/m/Y', $to)->endOfDay()
                    : Carbon::parse($to)->endOfDay();
            }
            if ($fromDate && $toDate) {
                $query->whereRaw($dateExpr.' BETWEEN ? AND ?', [$fromDate, $toDate]);
            } elseif ($fromDate) {
                $query->whereRaw($dateExpr.' >= ?', [$fromDate]);
            } elseif ($toDate) {
                $query->whereRaw($dateExpr.' <= ?', [$toDate]);
            }
        } catch (\Exception $e) { /* ignora datas inválidas */ }

        // Ordenação
        $allowed = [
            'occurred_at' => $dateExpr,
            'amount' => 'amount',
            'chat' => 'chat_id',
            'user' => 'user_id',
            'code' => 'code_subselect',
            'investment' => 'investment_name_subselect',
        ];
        $orderKey = $allowed[$sort] ?? $dateExpr;
        if($orderKey === $dateExpr){
            $query->orderByRaw($orderKey.' '.$dir);
        } elseif($orderKey === 'code_subselect') {
            $query->orderByRaw('(SELECT code FROM open_a_i_chats WHERE open_a_i_chats.id = openai_chat_records.chat_id) '.$dir);
        } elseif($orderKey === 'investment_name_subselect') {
            $query->orderByRaw('(SELECT account_name FROM investment_accounts WHERE investment_accounts.id = openai_chat_records.investment_account_id) '.$dir);
        } else {
            $query->orderBy($orderKey, $dir);
        }

        // Filtro por conta de investimento
        if ($invAccId !== null && $invAccId !== '') {
            if ((string)$invAccId === '0') {
                $query->whereNull('investment_account_id');
            } else {
                $query->where('investment_account_id', (int)$invAccId);
            }
        }

        if ($showAll) {
            $maxAll = 2000;
            $records = $query->limit($maxAll)->get();
        } else {
            $records = $query->paginate(25)->appends(array_filter([
                'chat_id' => $chatId ?: null,
                'from' => $from ?: null,
                'to' => $to ?: null,
                'sort' => $sort !== 'occurred_at' ? $sort : null,
                'dir' => ($dir !== 'desc') ? $dir : null,
                'all' => $showAll ? 1 : null,
                'investment_account_id' => ($invAccId !== null && $invAccId !== '') ? $invAccId : null,
            ]));
        }

        // Dados auxiliares
        $chats = OpenAIChat::where('user_id', Auth::id())->orderBy('title')->get(['id','title','code']);
        $selectedChat = null;
        if ($chatId > 0) {
            $selectedChat = $chats->firstWhere('id', $chatId);
        }
        $savedFilters = session('openai_records_saved_filters');
        $ordersQuery = \App\Models\OpenAICodeOrder::with(['chat:id,title,code','user:id,name'])
            ->where('user_id', Auth::id());
        if ($chatId > 0) {
            $ordersQuery->where('chat_id', $chatId);
        }
        $codeOrders = $ordersQuery->latest('created_at')->limit(50)->get();

    $investmentAccounts = InvestmentAccount::where('user_id', Auth::id())
            ->orderBy('account_name')
            ->get(['id','account_name','broker']);
    $lastInvestmentAccountId = (int) (session('last_investment_account_id') ?: 0) ?: null;

    return view('openai.records.index', compact('records','chats','chatId','selectedChat','from','to','showAll','sort','dir','savedFilters','varMode','codeOrders','investmentAccounts','invAccId','lastInvestmentAccountId','datesReapplied'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'chat_id' => 'required|integer|exists:open_a_i_chats,id',
            'occurred_at' => 'required', // parse manual abaixo para suportar dd/mm/yyyy
            'amount' => 'required|numeric',
            'investment_account_id' => 'nullable|integer|exists:investment_accounts,id',
        ]);

        // Garantir que o chat pertence ao usuário
        $chat = OpenAIChat::where('id', $validated['chat_id'])->where('user_id', Auth::id())->firstOrFail();
        $rawDate = trim((string)$validated['occurred_at']);
        $occurredAt = $this->parseOccurredAt($rawDate);
        if(!$occurredAt){
            return back()->withErrors(['occurred_at'=>'Data/hora inválida. Use dd/mm/AAAA HH:MM[:SS].'])->withInput();
        }

        // Checar ownership da conta de investimentos (se enviada)
        $invId = null;
        if (!empty($validated['investment_account_id'] ?? null)) {
            $ownAcc = \App\Models\InvestmentAccount::where('id', $validated['investment_account_id'])
                ->where('user_id', Auth::id())->exists();
            if (!$ownAcc) {
                return back()->withErrors(['investment_account_id' => 'Conta de investimento inválida.'])->withInput();
            }
            $invId = (int)$validated['investment_account_id'];
        }

        OpenAIChatRecord::create([
            'chat_id' => $chat->id,
            'user_id' => Auth::id(),
            'occurred_at' => $occurredAt,
            'amount' => $validated['amount'],
            'investment_account_id' => $invId,
        ]);

        // Memoriza última conta de investimento usada (se informada)
        if ($invId) {
            session(['last_investment_account_id' => $invId]);
        }

        return redirect()->route('openai.records.index', [
            'chat_id' => $chat->id,
        ])->with('success', 'Registro adicionado.');
    }

    public function destroy(OpenAIChatRecord $record): RedirectResponse
    {
        // Apenas dono do chat ou do registro
        if ((int)$record->user_id !== (int)Auth::id()) {
            abort(403);
        }
        $record->delete();
        return back()->with('success', 'Registro removido.');
    }

    public function edit(OpenAIChatRecord $record): View|RedirectResponse
    {
        if ((int)$record->user_id !== (int)Auth::id()) {
            abort(403);
        }
        $chats = OpenAIChat::where('user_id', Auth::id())->orderBy('title')->get(['id','title','code']);
        $investmentAccounts = \App\Models\InvestmentAccount::where('user_id', Auth::id())
            ->orderByDesc('date')->orderBy('account_name')
            ->get(['id','account_name','broker','date']);
        return view('openai.records.edit', [
            'record' => $record,
            'chats' => $chats,
            'investmentAccounts' => $investmentAccounts,
        ]);
    }

    public function update(Request $request, OpenAIChatRecord $record): RedirectResponse
    {
        if ((int)$record->user_id !== (int)Auth::id()) {
            abort(403);
        }
        // $originalOccurredAt = $record->occurred_at ? $record->occurred_at->copy() : null; // debug removido
        $validated = $request->validate([
            'chat_id' => 'required|integer|exists:open_a_i_chats,id',
            'occurred_at' => 'required',
            'amount' => 'required|numeric',
            'investment_account_id' => 'nullable|integer|exists:investment_accounts,id',
        ]);
        // Validar que chat pertence ao usuário
        $chat = OpenAIChat::where('id', $validated['chat_id'])->where('user_id', Auth::id())->firstOrFail();
        $rawDate = trim((string)$validated['occurred_at']);
        $occurredAt = $this->parseOccurredAt($rawDate);
        if(!$occurredAt){
            return back()->withErrors(['occurred_at'=>'Data/hora inválida. Use dd/mm/AAAA HH:MM[:SS].'])->withInput();
        }
        $record->chat_id = $chat->id;
        $record->amount = $validated['amount'];
        // Garantir que a conta pertence ao usuário (quando enviada)
        if (!empty($validated['investment_account_id'] ?? null)) {
            $ownAcc = \App\Models\InvestmentAccount::where('id', $validated['investment_account_id'])
                ->where('user_id', Auth::id())->exists();
            if (!$ownAcc) {
                return back()->withErrors(['investment_account_id' => 'Conta de investimento inválida.'])->withInput();
            }
            $record->investment_account_id = (int)$validated['investment_account_id'];
            // Memoriza última conta
            session(['last_investment_account_id' => (int)$validated['investment_account_id']]);
        } else {
            $record->investment_account_id = null;
        }
        $driver = DB::getDriverName();
        if ($driver === 'sqlsrv') {
            // Evita problemas de conversão no SQL Server: atualiza explicitamente só os campos necessários
            $originalOccurredAt = $record->getOriginal('occurred_at');
            $origCarbon = null;
            if ($originalOccurredAt instanceof \Carbon\Carbon) {
                $origCarbon = $originalOccurredAt;
            } elseif (!empty($originalOccurredAt)) {
                try { $origCarbon = \Carbon\Carbon::parse($originalOccurredAt); } catch (\Throwable $e) { $origCarbon = null; }
            }
            $occurredChanged = true;
            if ($origCarbon) {
                $occurredChanged = !$origCarbon->equalTo($occurredAt);
            }
            $updateData = [
                'chat_id' => $chat->id,
                'amount' => $validated['amount'],
                'investment_account_id' => $record->investment_account_id,
                // usar função nativa para updated_at e evitar conversão de string
                'updated_at' => DB::raw('SYSUTCDATETIME()'),
            ];
            if ($occurredChanged) {
                $updateData['occurred_at'] = $occurredAt->format('Y-m-d H:i:s');
            }
            try {
                DB::table('openai_chat_records')->where('id', $record->id)->update($updateData);
            } catch (QueryException $e) {
                // Tentativa em duas etapas como fallback: primeiro occurred_at, depois demais
                if (isset($updateData['occurred_at'])) {
                    DB::table('openai_chat_records')->where('id', $record->id)->update([
                        'occurred_at' => $updateData['occurred_at'],
                    ]);
                    unset($updateData['occurred_at']);
                }
                DB::table('openai_chat_records')->where('id', $record->id)->update($updateData);
            }
        } else {
            // Outros drivers seguem o fluxo normal do Eloquent
            $record->occurred_at = $occurredAt;
            $record->save();
        }
        return redirect()->route('openai.records.index', [
            'chat_id' => $chat->id,
        ])->with('success', 'Registro atualizado.');
    }

    /**
     * Faz o parse de data/hora sempre priorizando padrão brasileiro dd/mm/yyyy.
     * Aceita:
     * - dd/mm/yyyy
     * - dd/mm/yyyy HH:MM[:SS]
     * - ddmmyyyyHHMM[SS]
     * - ddmmyyyy (vira 00:00:00)
     * - datetime-local (yyyy-mm-ddTHH:MM[:SS])
     */
    private function parseOccurredAt(string $raw): ?Carbon
    {
        $raw = trim($raw);
        $raw = preg_replace('/\s+/', ' ', $raw);
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})(?:\s+(\d{2}):(\d{2})(?::(\d{2}))?)?$/', $raw, $m)) {
            $day=(int)$m[1]; $mon=(int)$m[2]; $year=(int)$m[3];
            $hour = isset($m[4]) ? (int)$m[4] : 0;
            $min  = isset($m[5]) ? (int)$m[5] : 0;
            $sec  = isset($m[6]) ? (int)$m[6] : 0;
            try {
                $dt = Carbon::create($year,$mon,$day,$hour,$min,$sec);
                // Verificação: se formatado não bate com entrada (parte data) mas trocar dia/mês bate, corrige.
                $entered = sprintf('%02d/%02d/%04d',$day,$mon,$year);
                if($dt->format('d/m/Y') !== $entered && $day <=12 && $mon <=12){
                    // tentar inversão
                    try {
                        $swapped = Carbon::create($year,$day,$mon,$hour,$min,$sec);
                        if($swapped->format('d/m/Y') === $entered){
                            $dt = $swapped;
                        }
                    } catch(\Exception $e2) {}
                }
                return $dt;
            } catch(\Exception $e) { return null; }
        }
        $digitsOnly = preg_replace('/\D/','',$raw);
        if (preg_match('/^\d{12,14}$/',$digitsOnly)) {
            if(strlen($digitsOnly)===12) $digitsOnly.='00';
            $day = (int)substr($digitsOnly,0,2); $mon = (int)substr($digitsOnly,2,2); $year = (int)substr($digitsOnly,4,4);
            $hour = (int)substr($digitsOnly,8,2); $min  = (int)substr($digitsOnly,10,2); $sec  = (int)substr($digitsOnly,12,2);
            try { return Carbon::create($year,$mon,$day,$hour,$min,$sec); } catch(\Exception $e) { return null; }
        }
        if (preg_match('/^\d{8}$/',$digitsOnly)) {
            $day = (int)substr($digitsOnly,0,2); $mon = (int)substr($digitsOnly,2,2); $year = (int)substr($digitsOnly,4,4);
            try { return Carbon::create($year,$mon,$day,0,0,0); } catch(\Exception $e) { return null; }
        }
        if (str_contains($raw,'T')) {
            try { return Carbon::parse($raw); } catch(\Exception $e) { return null; }
        }
        if (!str_contains($raw,'/')) {
            try { return Carbon::parse($raw); } catch(\Exception $e) { return null; }
        }
        return null;
    }

    public function assets(Request $request): View
    {
        $userId = (int) Auth::id();
        $from = $request->input('from');
        $to = $request->input('to');
        $invAccId = $request->input('investment_account_id');
    $sort = $request->input('sort', 'code'); // code|title|date|amount|account|qty
    $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        // Carregar contas de investimento do usuário para o filtro
        $investmentAccounts = InvestmentAccount::where('user_id', $userId)
            ->orderBy('account_name')
            ->get(['id','account_name','broker']);

        // Base: registros do usuário com join leve em chat para pegar title/code
        $q = OpenAIChatRecord::query()
            ->select('openai_chat_records.*')
            ->with(['chat:id,title,code','user:id,name','investmentAccount:id,account_name,broker'])
            ->where('openai_chat_records.user_id', $userId)
            ->join('open_a_i_chats as c', 'c.id', '=', 'openai_chat_records.chat_id');

        // Filtros de data
        if ($from) {
            try { $fromDate = \Carbon\Carbon::parse($from)->startOfDay(); $q->where('openai_chat_records.occurred_at', '>=', $fromDate); } catch (\Throwable $e) {}
        }
        if ($to) {
            try { $toDate = \Carbon\Carbon::parse($to)->endOfDay(); $q->where('openai_chat_records.occurred_at', '<=', $toDate); } catch (\Throwable $e) {}
        }
        if ($invAccId !== null && $invAccId !== '') {
            if ((string)$invAccId === '0') {
                $q->whereNull('openai_chat_records.investment_account_id');
            } else {
                $q->where('openai_chat_records.investment_account_id', (int)$invAccId);
            }
        }

        // Agrupar por código (ou título quando não houver código) e pegar o último registro por grupo
        // Estratégia: subquery para last_id por grupo e join de volta
        $driver = DB::getDriverName();
        // Atenção aos aliases nas subqueries: usamos 'cc' para open_a_i_chats
        $groupExpr = "ISNULL(NULLIF(LTRIM(RTRIM(cc.code)), ''), LTRIM(RTRIM(cc.title)))";
        if ($driver !== 'sqlsrv') {
            $groupExpr = "COALESCE(NULLIF(TRIM(cc.code), ''), TRIM(cc.title))";
        }

        // Subquery: conta por grupo e último id por grupo (maior occurred_at; se empate, maior id)
    $sub = DB::table('openai_chat_records as r')
            ->join('open_a_i_chats as cc', 'cc.id', '=', 'r.chat_id')
            ->selectRaw($groupExpr . ' as grp, COUNT(*) as qty, MAX(r.occurred_at) as max_dt');
        // Reaplica filtros ao sub
        $sub->where('r.user_id', $userId);
        if ($from) { try { $fromDate = \Carbon\Carbon::parse($from)->startOfDay(); $sub->where('r.occurred_at', '>=', $fromDate); } catch (\Throwable $e) {} }
        if ($to) { try { $toDate = \Carbon\Carbon::parse($to)->endOfDay(); $sub->where('r.occurred_at', '<=', $toDate); } catch (\Throwable $e) {} }
        if ($invAccId !== null && $invAccId !== '') {
            if ((string)$invAccId === '0') { $sub->whereNull('r.investment_account_id'); } else { $sub->where('r.investment_account_id', (int)$invAccId); }
        }
    // SQL Server não aceita alias no GROUP BY. Agrupar pela expressão completa.
    $sub->groupByRaw($groupExpr);
    $subSql = $sub->toSql();

    // Junta para pegar o registro mais recente de cada grupo (por occurred_at)
    $latest = DB::table('openai_chat_records as r')
            ->join('open_a_i_chats as cc', 'cc.id', '=', 'r.chat_id')
            ->join(DB::raw('(' . $subSql . ') as agg'), function($join) use ($groupExpr){
                $join->on(DB::raw($groupExpr), '=', 'agg.grp')
                     ->on('r.occurred_at', '=', 'agg.max_dt');
            })
            ->mergeBindings($sub);

        // Seleção final: apenas colunas do registro (r.*) + agregados necessários (grp, qty)
    $rows = $latest
            ->select('r.*', DB::raw('agg.grp as grp'), DB::raw('agg.qty as qty'))
            ->get();

        // Carregar modelos dos ids resultantes para ter relações eager-loaded facilmente
        $ids = collect($rows)->pluck('id')->unique()->values()->all();
        $records = OpenAIChatRecord::with(['chat:id,title,code','user:id,name','investmentAccount:id,account_name,broker'])->whereIn('id', $ids)->get();

        // Mapa quantidades por grupo
    $counts = collect($rows)->groupBy('grp')->map->first()->map(fn($r)=> (int)($r->qty ?? 0));
    $totalSelected = collect($rows)->sum(function($r){ return (int)($r->qty ?? 0); });

        // Ordenação dinâmica conforme solicitação
        $recordsSorted = $records->sortBy(function($r) use ($sort, $counts){
            $code = trim((string)($r->chat->code ?? ''));
            $title = trim((string)($r->chat->title ?? ''));
            $grp = $code !== '' ? $code : $title;
            return match($sort){
                'code' => mb_strtoupper($code),
                'title' => mb_strtoupper($title),
                'date' => $r->occurred_at ? $r->occurred_at->timestamp : 0,
                'amount' => (float)($r->amount ?? 0),
                'account' => mb_strtoupper(trim((string)($r->investmentAccount->account_name ?? ''))),
                'qty' => (int)($counts[$grp] ?? 0),
                default => mb_strtoupper($code),
            };
        }, SORT_NATURAL | SORT_FLAG_CASE, $dir === 'desc')->values();

        return view('openai.records.assets', [
            'records' => $recordsSorted,
            'counts' => $counts,
            'from' => $from,
            'to' => $to,
            'invAccId' => $invAccId,
            'investmentAccounts' => $investmentAccounts,
            'totalSelected' => $totalSelected,
            'sort' => $sort,
            'dir' => $dir,
        ]);
    }

    /**
     * Aplica a cotação informada ao valor do registro (amount).
     * Regras:
     * - Requer autenticação e permissão.
     * - Só permite para registros do próprio usuário.
     * - Atualiza apenas amount e updated_at.
     */
    public function applyQuote(Request $request, OpenAIChatRecord $record)
    {
        if ((int)$record->user_id !== (int)Auth::id()) {
            abort(403);
        }
        $validated = $request->validate([
            'amount' => 'required|numeric',
        ]);
        $newAmount = (float)$validated['amount'];
        $driver = DB::getDriverName();
        if ($driver === 'sqlsrv') {
            DB::table('openai_chat_records')->where('id', $record->id)->update([
                'amount' => $newAmount,
                'updated_at' => DB::raw('SYSUTCDATETIME()'),
            ]);
        } else {
            $record->amount = $newAmount;
            $record->save();
        }
        return response()->json([
            'ok' => true,
            'amount' => $newAmount,
        ]);
    }

    /**
     * Cria um novo registro a partir de uma cotação obtida.
     * Copia chat_id, user_id e investment_account_id do registro de referência.
     * Usa a data/hora informada (updated_at da cotação) como occurred_at.
     */
    public function createFromQuote(Request $request, OpenAIChatRecord $record)
    {
        if ((int)$record->user_id !== (int)Auth::id()) {
            abort(403);
        }
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'updated_at' => 'nullable|string',
        ]);
        $amount = (float)$validated['amount'];
        $rawWhen = (string)($validated['updated_at'] ?? '');
        // Tenta parsear a data/hora da cotação; se falhar, usa agora
        $when = null;
        try {
            if (trim($rawWhen) !== '') {
                $when = \Carbon\Carbon::parse($rawWhen);
            }
        } catch (\Throwable $e) { $when = null; }
        if (!$when) { $when = now(); }

        $driver = DB::getDriverName();
        if ($driver === 'sqlsrv') {
            DB::table('openai_chat_records')->insert([
                'chat_id' => $record->chat_id,
                'user_id' => Auth::id(),
                'occurred_at' => $when->format('Y-m-d H:i:s'),
                'amount' => $amount,
                'investment_account_id' => $record->investment_account_id,
                'created_at' => DB::raw('SYSUTCDATETIME()'),
                'updated_at' => DB::raw('SYSUTCDATETIME()'),
            ]);
        } else {
            OpenAIChatRecord::create([
                'chat_id' => $record->chat_id,
                'user_id' => Auth::id(),
                'occurred_at' => $when,
                'amount' => $amount,
                'investment_account_id' => $record->investment_account_id,
            ]);
        }
        return response()->json(['ok' => true]);
    }
}
