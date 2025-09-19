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
    // Filtro por ativo (código ou título da conversa)
    $asset = trim((string)$request->input('asset', ''));
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

        // Filtro por ativo (código/título) limitado às conversas do tipo "BOLSA DE VALORES AMERICANA"
        if ($asset !== '') {
            $like = '%'.$asset.'%';
            $query->whereHas('chat', function($q) use ($like){
                $q->where(function($qq) use ($like){
                    $qq->where('code','like',$like)
                       ->orWhere('title','like',$like);
                })->whereHas('type', function($t){
                    $t->whereRaw('UPPER(name) = ?', ['BOLSA DE VALORES AMERICANA']);
                });
            });
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
                'asset' => $asset !== '' ? $asset : null,
            ]));
        }

        // Dados auxiliares - chats somente do tipo "BOLSA DE VALORES AMERICANA"
        $chats = OpenAIChat::where('user_id', Auth::id())
            ->whereHas('type', function($q){
                $q->whereRaw('UPPER(name) = ?', ['BOLSA DE VALORES AMERICANA']);
            })
            ->orderBy('title')
            ->get(['id','title','code']);
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

        // Combo de Ativo (código/título) baseado nas conversas do tipo "BOLSA DE VALORES AMERICANA"
        $assetOptions = OpenAIChat::where('user_id', Auth::id())
            ->whereHas('type', function($q){
                $q->whereRaw('UPPER(name) = ?', ['BOLSA DE VALORES AMERICANA']);
            })
            ->where(function($q){
                $q->whereNotNull('code')->whereRaw("LTRIM(RTRIM(code)) <> ''")
                  ->orWhere(function($q2){ $q2->whereNotNull('title')->whereRaw("LTRIM(RTRIM(title)) <> ''"); });
            })
            ->orderByRaw("COALESCE(NULLIF(LTRIM(RTRIM(code)), ''), LTRIM(RTRIM(title)))")
            ->get(['code','title'])
            ->map(function($c){
                $code = trim((string)($c->code ?? ''));
                $title = trim((string)($c->title ?? ''));
                $label = $code !== '' ? $code : $title;
                $text = $code !== '' ? ($code . ' — ' . $title) : $title;
                return ['label' => $label, 'text' => $text];
            })
            ->unique('label')
            ->values();

    return view('openai.records.index', compact('records','chats','chatId','selectedChat','from','to','showAll','sort','dir','savedFilters','varMode','codeOrders','investmentAccounts','invAccId','lastInvestmentAccountId','datesReapplied','assetOptions','asset'));
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

        // Modo CHECK: se ativado, atualizar se já existir registro do mesmo chat na mesma data; senão, criar novo
        $checkUpdate = $request->boolean('check_update');
        $driver = DB::getDriverName();
        $dateOnly = $occurredAt->format('Y-m-d');

        if ($checkUpdate) {
            // Busca por registro existente do mesmo chat e mesma data (ignorando hora)
            if ($driver === 'sqlsrv') {
                $existing = OpenAIChatRecord::where('chat_id', $chat->id)
                    ->whereRaw("CONVERT(date, occurred_at) = ?", [$dateOnly])
                    ->orderByDesc('occurred_at')
                    ->first();
            } else {
                $existing = OpenAIChatRecord::where('chat_id', $chat->id)
                    ->whereDate('occurred_at', $dateOnly)
                    ->orderByDesc('occurred_at')
                    ->first();
            }

            if ($existing) {
                // Atualiza apenas o amount e opcionalmente a occurred_at para a nova hora informada
                if ($driver === 'sqlsrv') {
                    DB::table('openai_chat_records')->where('id', $existing->id)->update([
                        'amount' => $validated['amount'],
                        'occurred_at' => $occurredAt->format('Y-m-d H:i:s'),
                        'investment_account_id' => $invId,
                        'updated_at' => DB::raw('SYSUTCDATETIME()'),
                    ]);
                } else {
                    $existing->amount = $validated['amount'];
                    $existing->occurred_at = $occurredAt;
                    $existing->investment_account_id = $invId;
                    $existing->save();
                }

                // Memoriza última conta de investimento usada (se informada)
                if ($invId) {
                    session(['last_investment_account_id' => $invId]);
                }

                return redirect()->route('openai.records.index', [
                    'chat_id' => $chat->id,
                ])->with('success', 'Registro atualizado (CHECK ativo).');
            }
        }

        // Caso não haja CHECK ou não exista registro na data, inserir novo
        if ($driver === 'sqlsrv') {
            DB::table('openai_chat_records')->insert([
                'chat_id' => $chat->id,
                'user_id' => Auth::id(),
                'occurred_at' => $occurredAt->format('Y-m-d H:i:s'),
                'amount' => $validated['amount'],
                'investment_account_id' => $invId,
                'created_at' => DB::raw('SYSUTCDATETIME()'),
                'updated_at' => DB::raw('SYSUTCDATETIME()'),
            ]);
        } else {
            OpenAIChatRecord::create([
                'chat_id' => $chat->id,
                'user_id' => Auth::id(),
                'occurred_at' => $occurredAt,
                'amount' => $validated['amount'],
                'investment_account_id' => $invId,
            ]);
        }

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
        // Apenas chats do tipo "BOLSA DE VALORES AMERICANA"
        $chats = OpenAIChat::where('user_id', Auth::id())
            ->whereHas('type', function($q){
                $q->whereRaw('UPPER(name) = ?', ['BOLSA DE VALORES AMERICANA']);
            })
            ->orderBy('title')
            ->get(['id','title','code']);
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
    // Filtro por ativo (agora: somente código da conversa)
    $assetFilter = trim((string)$request->input('asset', ''));
    $assetCode = $assetFilter !== '' ? strtoupper($assetFilter) : '';
    // Novo filtro: mostrar apenas ativos cujo último registro NÃO é posterior a esta data (no_after)
    // Interpretação: se informado YYYY-MM-DD, só incluir grupos cujo max(occurred_at) <= fim do dia informado.
    // (Se o usuário desejar posteriormente a modalidade de igualdade estrita, poderemos ampliar com outro parâmetro.)
    $noAfter = $request->input('no_after');
    $baseline = $request->input('baseline'); // data base para comparação
        $excludeDate = $request->input('exclude_date'); // YYYY-MM-DD
        $invAccId = $request->input('investment_account_id');
    $sort = $request->input('sort', 'code'); // code|title|date|amount|account|qty
    $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        // Carregar contas de investimento do usuário para o filtro
        $investmentAccounts = InvestmentAccount::where('user_id', $userId)
            ->orderBy('account_name')
            ->get(['id','account_name','broker']);

        // Combo de Ativo (apenas CÓDIGOS) baseado nas conversas do tipo "BOLSA DE VALORES AMERICANA"
        // Mantemos apenas chats do usuário com code não vazio, ordenados pelo code e únicos.
        $assetOptions = \App\Models\OpenAIChat::where('user_id', $userId)
            ->whereHas('type', function($q){
                $q->whereRaw('UPPER(name) = ?', ['BOLSA DE VALORES AMERICANA']);
            })
            ->whereNotNull('code')
            ->whereRaw("LTRIM(RTRIM(code)) <> ''")
            ->select('code')
            ->groupBy('code')
            ->orderByRaw("UPPER(LTRIM(RTRIM(code)))")
            ->get()
            ->map(function($c){
                $code = strtoupper(trim((string)($c->code ?? '')));
                return ['label' => $code, 'text' => $code];
            })
            ->unique('label')
            ->values();

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
        // Filtro por CÓDIGO exato (aplica nas consultas base)
        $driver = DB::getDriverName();
        $codeExpr = $driver === 'sqlsrv' ? "UPPER(LTRIM(RTRIM(cc.code)))" : "UPPER(TRIM(cc.code))";
        if ($assetCode !== '') {
            $q->whereRaw($codeExpr . ' = ?', [$assetCode]);
        }

        // Agrupar por código (ou título quando não houver código) e pegar o último registro por grupo
        // Estratégia: subquery para last_id por grupo e join de volta
        $driver = DB::getDriverName();
        // Atenção aos aliases nas subqueries: usamos 'cc' para open_a_i_chats
        $groupExpr = "ISNULL(NULLIF(LTRIM(RTRIM(cc.code)), ''), LTRIM(RTRIM(cc.title)))";
        if ($driver !== 'sqlsrv') {
            $groupExpr = "COALESCE(NULLIF(TRIM(cc.code), ''), TRIM(cc.title))";
        }

    // Subquery: conta por grupo e último id por grupo (maior occurred_at; se empate, maior id) + média no período filtrado
    $sub = DB::table('openai_chat_records as r')
            ->join('open_a_i_chats as cc', 'cc.id', '=', 'r.chat_id')
        ->selectRaw($groupExpr . ' as grp, COUNT(*) as qty, MAX(r.occurred_at) as max_dt, AVG(CAST(r.amount as float)) as avg_amt');
        // Reaplica filtros ao sub
        $sub->where('r.user_id', $userId);
        if ($assetCode !== '') { $sub->whereRaw($codeExpr . ' = ?', [$assetCode]); }
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
        if ($assetCode !== '') { $latest->whereRaw($codeExpr . ' = ?', [$assetCode]); }

        // Seleção final: apenas colunas do registro (r.*) + agregados necessários (grp, qty)
        $rows = $latest
            ->select('r.*', DB::raw('agg.grp as grp'), DB::raw('agg.qty as qty'), DB::raw('agg.avg_amt as avg_amt'))
            ->get();

        // Filtro por código já aplicado nas consultas; não é mais necessário filtrar por título/parcial aqui

        // Baselines: para cada grupo, pegar o primeiro registro com occurred_at >= baseline
        $baselines = collect();
    $statsService = app(\App\Services\AssetStatsService::class);
    if ($baseline) {
            try { $baselineDt = \Carbon\Carbon::parse($baseline)->startOfDay(); } catch (\Throwable $e) { $baselineDt = null; }
            if ($baselineDt) {
                $subBase = DB::table('openai_chat_records as r')
                    ->join('open_a_i_chats as cc', 'cc.id', '=', 'r.chat_id')
                    ->selectRaw($groupExpr . ' as grp, MIN(r.occurred_at) as min_dt')
                    ->where('r.user_id', $userId)
                    ->where('r.occurred_at', '>=', $baselineDt);
                if ($assetCode !== '') { $subBase->whereRaw($codeExpr . ' = ?', [$assetCode]); }
                if ($invAccId !== null && $invAccId !== '') {
                    if ((string)$invAccId === '0') { $subBase->whereNull('r.investment_account_id'); }
                    else { $subBase->where('r.investment_account_id', (int)$invAccId); }
                }
                $subBase->groupByRaw($groupExpr);
                $subBaseSql = $subBase->toSql();

                $baseRows = DB::table('openai_chat_records as r')
                    ->join('open_a_i_chats as cc', 'cc.id', '=', 'r.chat_id')
                    ->join(DB::raw('(' . $subBaseSql . ') as b'), function($join) use ($groupExpr){
                        $join->on(DB::raw($groupExpr), '=', 'b.grp')
                             ->on('r.occurred_at', '=', 'b.min_dt');
                    })
                    ->mergeBindings($subBase)
                    ->when($assetCode !== '', function($q) use ($codeExpr, $assetCode){ return $q->whereRaw($codeExpr.' = ?', [$assetCode]); })
                    ->select(DB::raw('b.grp as grp'), 'r.amount', 'r.occurred_at')
                    ->get();
                $pairs = [];
                foreach ($baseRows as $row) {
                    $pairs[$row->grp] = [
                        'amount' => (float) $row->amount,
                        'occurred_at' => \Carbon\Carbon::parse($row->occurred_at),
                    ];
                }
                $baselines = collect($pairs);
            }
        }

        // Se pediu exclusão e há baseline, removemos grupos cuja data do registro base (coluna Var/Dif) == exclude_date
        if ($excludeDate && $baseline && $baselines->isNotEmpty()) {
            try {
                $ex = \Carbon\Carbon::parse($excludeDate)->format('Y-m-d');
                $rows = $rows->filter(function($row) use ($baselines, $ex){
                    $grp = $row->grp;
                    $b = $baselines->get($grp);
                    if (!$b || empty($b['occurred_at'])) { return true; }
                    try { $bd = \Carbon\Carbon::parse($b['occurred_at'])->format('Y-m-d'); } catch (\Throwable $e) { return true; }
                    return $bd !== $ex;
                })->values();
            } catch (\Throwable $e) { /* ignora formato inválido */ }
        }

        // Filtro adicional: remover grupos que possuam registros posteriores à data limite (no_after)
        if ($noAfter) {
            try {
                $limitDt = \Carbon\Carbon::parse($noAfter)->endOfDay();
                // Obter max global (sem filtros de data from/to) por grupo para o usuário
                $globalSub = DB::table('openai_chat_records as r')
                    ->join('open_a_i_chats as cc', 'cc.id', '=', 'r.chat_id')
                    ->where('r.user_id', $userId)
                    ->when($assetCode !== '', function($q) use ($codeExpr, $assetCode){ return $q->whereRaw($codeExpr.' = ?', [$assetCode]); })
                    ->selectRaw($groupExpr . ' as grp, MAX(r.occurred_at) as gmax')
                    ->groupByRaw($groupExpr)
                    ->pluck('gmax', 'grp');
                $rows = $rows->filter(function($row) use ($globalSub, $limitDt){
                    if (!isset($globalSub[$row->grp])) { return false; }
                    try { $gmax = \Carbon\Carbon::parse($globalSub[$row->grp]); } catch (\Throwable $e) { return false; }
                    return $gmax->lessThanOrEqualTo($limitDt);
                })->values();
            } catch (\Throwable $e) { /* formato inválido -> ignora filtro */ }
        }

        // Carregar modelos dos ids resultantes para ter relações eager-loaded facilmente
        $ids = collect($rows)->pluck('id')->unique()->values()->all();
        $records = OpenAIChatRecord::with(['chat:id,title,code','user:id,name','investmentAccount:id,account_name,broker'])->whereIn('id', $ids)->get();

        // Mapa quantidades por grupo e total (após aplicar filtros)
        $counts = collect($rows)->groupBy('grp')->map->first()->map(fn($r)=> (int)($r->qty ?? 0));
        // Mapa médias por grupo
        $averages = collect($rows)->groupBy('grp')->map->first()->map(function($r){
            $v = $r->avg_amt ?? null; return $v !== null ? (float)$v : null;
        });
        // Estatísticas até a baseline (se informada): considerar somente registros <= fim do dia baseline
        $baselineStats = collect();
        // Estatísticas gerais (independentes de baseline) para o intervalo filtrado
        $overallStats = collect();

        // Cache key base
        $cacheKeyBase = 'assets_stats:' . $userId . ':' . md5(json_encode([
            'from'=>$from,'to'=>$to,'inv'=>$invAccId,'baseline'=>$baseline,'noAfter'=>$noAfter
        ]));
        if (function_exists('cache')) {
            $cachedBaseline = cache()->get($cacheKeyBase.':baseline');
            $cachedOverall = cache()->get($cacheKeyBase.':overall');
            if ($cachedBaseline instanceof \Illuminate\Support\Collection) { $baselineStats = $cachedBaseline; }
            if ($cachedOverall instanceof \Illuminate\Support\Collection) { $overallStats = $cachedOverall; }
        }

        if ($baseline) {
            try { $baselineEnd = \Carbon\Carbon::parse($baseline)->endOfDay(); } catch (\Throwable $e) { $baselineEnd = null; }
            if ($baselineEnd) {
                // Buscar registros para os grupos já selecionados respeitando filtros existentes + occurred_at <= baselineEnd
                $grpKeys = collect($rows)->pluck('grp')->unique()->values();
                if ($grpKeys->isNotEmpty()) {
                    $statsQuery = DB::table('openai_chat_records as r')
                        ->join('open_a_i_chats as cc', 'cc.id', '=', 'r.chat_id')
                        ->where('r.user_id', $userId)
                        ->where('r.occurred_at', '<=', $baselineEnd);
                    if ($assetCode !== '') { $statsQuery->whereRaw($codeExpr . ' = ?', [$assetCode]); }
                    if ($from) { try { $fromDate = \Carbon\Carbon::parse($from)->startOfDay(); $statsQuery->where('r.occurred_at', '>=', $fromDate); } catch (\Throwable $e) {} }
                    if ($to) { try { $toDate = \Carbon\Carbon::parse($to)->endOfDay(); $statsQuery->where('r.occurred_at', '<=', $toDate); } catch (\Throwable $e) {} }
                    if ($invAccId !== null && $invAccId !== '') {
                        if ((string)$invAccId === '0') { $statsQuery->whereNull('r.investment_account_id'); }
                        else { $statsQuery->where('r.investment_account_id', (int)$invAccId); }
                    }
                    // Selecionar quantidades individuais para calcular mediana em PHP
                    $statsRows = $statsQuery->selectRaw($groupExpr . ' as grp, r.amount')->get();
                    $tmp = [];
                    foreach ($statsRows as $sr) {
                        $g = $sr->grp;
                        if (!$grpKeys->contains($g)) continue; // só grupos exibidos
                        $amt = (float)($sr->amount ?? 0);
                        if (!isset($tmp[$g])) {
                            $tmp[$g] = ['sum'=>0.0,'count'=>0,'min'=>null,'max'=>null,'values'=>[]];
                        }
                        $tmp[$g]['sum'] += $amt;
                        $tmp[$g]['count'] += 1;
                        $tmp[$g]['min'] = $tmp[$g]['min'] === null ? $amt : min($tmp[$g]['min'], $amt);
                        $tmp[$g]['max'] = $tmp[$g]['max'] === null ? $amt : max($tmp[$g]['max'], $amt);
                        $tmp[$g]['values'][] = $amt;
                    }
                    foreach ($tmp as $g => $data) {
                        if ($data['count'] === 0) continue;
                        $baselineStats[$g] = $statsService->compute($data['values']);
                    }
                    if (isset($statsRows) && function_exists('cache') && $baselineStats->isNotEmpty()) {
                        cache()->put($cacheKeyBase.':baseline', $baselineStats, 300); // 5 min
                    }
                }
            }
        }
        // Estatísticas gerais (sem limite baseline, mas respeitando filtros from/to/conta)
        if ($overallStats->isEmpty()) {
            $grpKeysAll = collect($rows)->pluck('grp')->unique()->values();
            if ($grpKeysAll->isNotEmpty()) {
                $oQuery = DB::table('openai_chat_records as r')
                    ->join('open_a_i_chats as cc', 'cc.id', '=', 'r.chat_id')
                    ->where('r.user_id', $userId);
                if ($assetCode !== '') { $oQuery->whereRaw($codeExpr . ' = ?', [$assetCode]); }
                if ($from) { try { $fromDate = \Carbon\Carbon::parse($from)->startOfDay(); $oQuery->where('r.occurred_at', '>=', $fromDate); } catch (\Throwable $e) {} }
                if ($to) { try { $toDate = \Carbon\Carbon::parse($to)->endOfDay(); $oQuery->where('r.occurred_at', '<=', $toDate); } catch (\Throwable $e) {} }
                if ($invAccId !== null && $invAccId !== '') {
                    if ((string)$invAccId === '0') { $oQuery->whereNull('r.investment_account_id'); }
                    else { $oQuery->where('r.investment_account_id', (int)$invAccId); }
                }
                $oRows = $oQuery->selectRaw($groupExpr . ' as grp, r.amount')->get();
                $tmp2 = [];
                foreach ($oRows as $or) {
                    $g = $or->grp;
                    if (!$grpKeysAll->contains($g)) continue;
                    $amt = (float)($or->amount ?? 0);
                    if (!isset($tmp2[$g])) { $tmp2[$g] = ['sum'=>0.0,'count'=>0,'min'=>null,'max'=>null,'values'=>[]]; }
                    $tmp2[$g]['sum'] += $amt;
                    $tmp2[$g]['count'] += 1;
                    $tmp2[$g]['min'] = $tmp2[$g]['min'] === null ? $amt : min($tmp2[$g]['min'], $amt);
                    $tmp2[$g]['max'] = $tmp2[$g]['max'] === null ? $amt : max($tmp2[$g]['max'], $amt);
                    $tmp2[$g]['values'][] = $amt;
                }
                foreach ($tmp2 as $g => $data) {
                    if ($data['count'] === 0) continue;
                    $overallStats[$g] = $statsService->compute($data['values']);
                }
                if (isset($oRows) && function_exists('cache') && $overallStats->isNotEmpty()) {
                    cache()->put($cacheKeyBase.':overall', $overallStats, 300);
                }
            }
        }
        $totalSelected = collect($rows)->sum(function($r){ return (int)($r->qty ?? 0); });

        // Ordenação dinâmica conforme solicitação
        $recordsSorted = $records->sortBy(function($r) use ($sort, $counts, $baselines, $averages, $baselineStats, $overallStats){
            $code = trim((string)($r->chat->code ?? ''));
            $title = trim((string)($r->chat->title ?? ''));
            $grp = $code !== '' ? $code : $title;
            $base = null;
            if (isset($baselines) && $baselines instanceof \Illuminate\Support\Collection) {
                $base = $baselines->get($grp)['amount'] ?? null;
            }
            $cur = (float)($r->amount ?? 0);
            $dif = ($base !== null) ? ($cur - (float)$base) : null;
            $var = ($base && abs((float)$base) > 0.0000001) ? (($cur - (float)$base) / (float)$base * 100.0) : null;
            // usar média baseline se disponível
            $avg = $baselineStats->get($grp)['avg'] ?? ($averages[$grp] ?? null);
            $median = $baselineStats->get($grp)['median'] ?? ($overallStats->get($grp)['median'] ?? null);
            $maxV = $baselineStats->get($grp)['max'] ?? ($overallStats->get($grp)['max'] ?? null);
            $minV = $baselineStats->get($grp)['min'] ?? ($overallStats->get($grp)['min'] ?? null);
            $countBase = $baselineStats->get($grp)['count'] ?? null;
            $countTotal = $overallStats->get($grp)['count'] ?? null;
            $avgTotal = $overallStats->get($grp)['avg'] ?? null;
            $medianTotal = $overallStats->get($grp)['median'] ?? null;
            $maxTotal = $overallStats->get($grp)['max'] ?? null;
            $minTotal = $overallStats->get($grp)['min'] ?? null;
            return match($sort){
                'code' => mb_strtoupper($code),
                'title' => mb_strtoupper($title),
                'date' => $r->occurred_at ? $r->occurred_at->timestamp : 0,
                'amount' => (float)($r->amount ?? 0),
                'account' => mb_strtoupper(trim((string)($r->investmentAccount->account_name ?? ''))),
                'qty' => (int)($counts[$grp] ?? 0),
                'var' => $var !== null ? (float)$var : -INF,
                'diff' => $dif !== null ? (float)$dif : -INF,
                'avg' => $avg !== null ? (float)$avg : -INF,
                'median' => $median !== null ? (float)$median : -INF,
                'max' => $maxV !== null ? (float)$maxV : -INF,
                'min' => $minV !== null ? (float)$minV : -INF,
                'count_base' => $countBase !== null ? (float)$countBase : -INF,
                'count_total' => $countTotal !== null ? (float)$countTotal : -INF,
                'avg_total' => $avgTotal !== null ? (float)$avgTotal : -INF,
                'median_total' => $medianTotal !== null ? (float)$medianTotal : -INF,
                'max_total' => $maxTotal !== null ? (float)$maxTotal : -INF,
                'min_total' => $minTotal !== null ? (float)$minTotal : -INF,
                default => mb_strtoupper($code),
            };
        }, SORT_NATURAL | SORT_FLAG_CASE, $dir === 'desc')->values();

    return view('openai.records.assets', [
            'records' => $recordsSorted,
            'counts' => $counts,
            'from' => $from,
            'to' => $to,
        'no_after' => $noAfter,
            'baseline' => $baseline,
            'exclude_date' => $excludeDate,
            'invAccId' => $invAccId,
        'asset' => $assetFilter,
            'investmentAccounts' => $investmentAccounts,
            'assetOptions' => $assetOptions,
            'totalSelected' => $totalSelected,
            'sort' => $sort,
            'dir' => $dir,
            'baselines' => $baselines,
        'averages' => $averages, // média completa (sem baseline) – usada fallback
        'baselineStats' => $baselineStats, // estatísticas limitadas até baseline
        'overallStats' => $overallStats, // estatísticas gerais sem limite baseline
        ]);
    }

    /**
     * Exporta a visão de assets em CSV incluindo estatísticas baseline.
     */
    public function assetsExport(Request $request)
    {
        // Reutiliza lógica de assets() executando internamente e recolhendo dados já prontos.
        // Para evitar duplicação grande, chamaremos assets() parcialmente refatorado no futuro.
        // Aqui replicamos apenas o essencial de leitura (mantendo consistência com assets()).
        $responseView = $this->assets($request); // View com dados compactados
        $data = $responseView->getData();
    $records = $data['records'] ?? collect();
    $baselines = $data['baselines'] ?? collect();
    $baselineStats = $data['baselineStats'] ?? collect();
    $overallStats = $data['overallStats'] ?? collect();
    $counts = $data['counts'] ?? collect();
        $baseline = $data['baseline'] ?? null;
        $locale = strtolower((string)$request->input('locale'));
        $ptBR = $locale === 'br' || $locale === 'pt-br';
        $callback = function() use ($records, $baselines, $baselineStats, $counts, $baseline, $overallStats, $ptBR){
            $out = fopen('php://output', 'w');
            // Cabeçalho
            fputcsv($out, [
                'Codigo','Conversa','DataUltimo','ValorUltimo','Qtd',
                'BaseValor','BaseData','VarPercent','Dif',
                'MediaBase','MedianaBase','MaxBase','MinBase','CountBase',
                'MediaTotal','MedianaTotal','MaxTotal','MinTotal','CountTotal'
            ], ';');
            foreach ($records as $r) {
                $code = trim($r->chat->code ?? '') ?: trim($r->chat->title ?? '');
                $b = $baselines->get($code) ?? null;
                $baseAmount = $b['amount'] ?? null;
                $baseDate = isset($b['occurred_at']) ? (optional($b['occurred_at'])->format('Y-m-d')) : null;
                $cur = (float)($r->amount ?? 0);
                $dif = ($baseAmount !== null) ? ($cur - (float)$baseAmount) : null;
                $var = ($baseAmount !== null && abs((float)$baseAmount) > 0.0000001) ? ($dif / (float)$baseAmount * 100.0) : null;
                $stats = $baselineStats->get($code) ?? [];
                $statsAll = $overallStats->get($code) ?? [];
                // Função util formatação pt-BR opcional
                $fmt = function($n, $dec=6) use ($ptBR){
                    if ($n === '' || $n === null) return '';
                    $n = (float)$n;
                    return $ptBR ? number_format($n, $dec, ',', '.') : number_format($n, $dec, '.', '');
                };
                $fmtDate = function($dtStr) use ($ptBR){
                    if (!$dtStr) return '';
                    if (!$ptBR) return $dtStr; // já ISO
                    // espera formatos Y-m-d ou Y-m-d H:i:s
                    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})(?: (\d{2}:\d{2}:\d{2}))?$/', $dtStr, $m)) {
                        $d = $m[3].'/'.$m[2].'/'.$m[1];
                        if (!empty($m[4])) return $d.' '.$m[4];
                        return $d;
                    }
                    return $dtStr;
                };
                $row = [
                    $code,
                    $r->chat->title ?? '',
                    $fmtDate(optional($r->occurred_at)->format('Y-m-d H:i:s')),
                    $fmt($cur),
                    (int)($counts[$code] ?? 0),
                    $baseAmount !== null ? $fmt($baseAmount) : '',
                    $fmtDate($baseDate),
                    $var !== null ? $fmt($var) : '',
                    $dif !== null ? $fmt($dif) : '',
                    isset($stats['avg']) ? $fmt($stats['avg']) : '',
                    isset($stats['median']) ? $fmt($stats['median']) : '',
                    isset($stats['max']) ? $fmt($stats['max']) : '',
                    isset($stats['min']) ? $fmt($stats['min']) : '',
                    isset($stats['count']) ? (int)$stats['count'] : '',
                    isset($statsAll['avg']) ? $fmt($statsAll['avg']) : '',
                    isset($statsAll['median']) ? $fmt($statsAll['median']) : '',
                    isset($statsAll['max']) ? $fmt($statsAll['max']) : '',
                    isset($statsAll['min']) ? $fmt($statsAll['min']) : '',
                    isset($statsAll['count']) ? (int)$statsAll['count'] : '',
                ];
                fputcsv($out, $row, ';');
            }
            fclose($out);
        };
        $fileName = 'assets_export_' . date('Ymd_His') . '.csv';
        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
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
