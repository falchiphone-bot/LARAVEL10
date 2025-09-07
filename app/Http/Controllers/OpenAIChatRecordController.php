<?php

namespace App\Http\Controllers;

use App\Models\OpenAIChat;
use App\Models\OpenAIChatRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class OpenAIChatRecordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    $this->middleware(['permission:OPENAI - CHAT'])->only('index','store','destroy','edit','update');
    }

    public function index(Request $request): View
    {
        $chatId = (int) $request->input('chat_id');
        $from = $request->input('from');
        $to = $request->input('to');
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
    $sort = $request->input('sort','occurred_at');
    $dir = strtolower($request->input('dir','desc')) === 'asc' ? 'asc' : 'desc';
    $query = OpenAIChatRecord::with(['chat:id,title,code','user:id,name']);
    $showAll = (bool)$request->input('all');


        if ($chatId > 0) {
            $query->where('chat_id', $chatId);
        }
        // Filtros de data com bindings seguros (evita formato inválido para SQL Server)
        $dateExpr = 'occurred_at';
        $driver = DB::getDriverName();
        // Detectar se coluna ainda é texto no SQL Server (causa erro de conversão implícita)
        if ($driver === 'sqlsrv') {
            try {
                $colInfo = DB::selectOne("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='openai_chat_records' AND COLUMN_NAME='occurred_at'");
                $dataType = $colInfo->DATA_TYPE ?? null;
                if ($dataType && !in_array(strtolower($dataType), ['datetime','datetime2','smalldatetime','date'])) {
                    // usar conversão explícita estilo 103 (dd/mm/yyyy)
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
    // Ordenação usa mesma expressão para evitar conversão implícita problemática
        // Mapear sort permitido
        $allowed = [
            'occurred_at' => $dateExpr,
            'amount' => 'amount',
            'chat' => 'chat_id',
            'user' => 'user_id',
            'code' => 'code_subselect', // marcador especial
        ];
        $orderKey = $allowed[$sort] ?? $dateExpr;
        if($orderKey === $dateExpr){
            $query->orderByRaw($orderKey.' '.$dir);
        } elseif($orderKey === 'code_subselect') {
            // Ordenar por código da conversa (subselect evita join adicional)
            $query->orderByRaw('(SELECT code FROM open_a_i_chats WHERE open_a_i_chats.id = openai_chat_records.chat_id) '.$dir);
        } else {
            $query->orderBy($orderKey, $dir);
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
            ]));
        }


        // dd($records);
        $chats = OpenAIChat::where('user_id', Auth::id())->orderBy('title')->get(['id','title','code']);
        $selectedChat = null;
        if ($chatId > 0) {
            $selectedChat = $chats->firstWhere('id', $chatId);
        }
    $savedFilters = session('openai_records_saved_filters');
    return view('openai.records.index', compact('records','chats','chatId','selectedChat','from','to','showAll','sort','dir','savedFilters','varMode'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'chat_id' => 'required|integer|exists:open_a_i_chats,id',
            'occurred_at' => 'required', // parse manual abaixo para suportar dd/mm/yyyy
            'amount' => 'required|numeric',
        ]);

        // Garantir que o chat pertence ao usuário
        $chat = OpenAIChat::where('id', $validated['chat_id'])->where('user_id', Auth::id())->firstOrFail();
        $rawDate = trim((string)$validated['occurred_at']);
        $occurredAt = $this->parseOccurredAt($rawDate);
        if(!$occurredAt){
            return back()->withErrors(['occurred_at'=>'Data/hora inválida. Use dd/mm/AAAA HH:MM[:SS].'])->withInput();
        }

        OpenAIChatRecord::create([
            'chat_id' => $chat->id,
            'user_id' => Auth::id(),
            'occurred_at' => $occurredAt,
            'amount' => $validated['amount'],
        ]);

    $from = $occurredAt->format('Y-m-d');
    return redirect()->route('openai.records.index', [
        'chat_id' => $chat->id,
        'from' => $from,
        'to' => $from,
        ])
            ->with('success', 'Registro adicionado.');
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
        return view('openai.records.edit', [
            'record' => $record,
            'chats' => $chats,
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
        ]);
        // Validar que chat pertence ao usuário
        $chat = OpenAIChat::where('id', $validated['chat_id'])->where('user_id', Auth::id())->firstOrFail();
        $rawDate = trim((string)$validated['occurred_at']);
        $occurredAt = $this->parseOccurredAt($rawDate);
        if(!$occurredAt){
            return back()->withErrors(['occurred_at'=>'Data/hora inválida. Use dd/mm/AAAA HH:MM[:SS].'])->withInput();
        }
        $record->chat_id = $chat->id;
        $record->occurred_at = $occurredAt;
        $record->amount = $validated['amount'];
        $record->save();
        return redirect()->route('openai.records.index', [
            'chat_id' => $chat->id,
            'from' => $occurredAt->format('Y-m-d'),
            'to' => $occurredAt->format('Y-m-d'),
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
}
