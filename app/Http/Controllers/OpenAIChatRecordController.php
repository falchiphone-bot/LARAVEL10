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
        $query = OpenAIChatRecord::with(['chat:id,title,code','user:id,name']);
        if ($chatId > 0) {
            $query->where('chat_id', $chatId);
        }
        if ($from) {
            try {
                if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $from)) {
                    $fromDate = Carbon::createFromFormat('d/m/Y', $from)->startOfDay();
                } else {
                    $fromDate = Carbon::parse($from)->startOfDay();
                }
                $query->where('occurred_at', '>=', $fromDate);
            } catch (\Exception $e) {
                // ignorar parse inválido silenciosamente
            }
        }
        if ($to) {
            try {
                if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $to)) {
                    $toDate = Carbon::createFromFormat('d/m/Y', $to)->endOfDay();
                } else {
                    $toDate = Carbon::parse($to)->endOfDay();
                }
                $query->where('occurred_at', '<=', $toDate);
            } catch (\Exception $e) {
                // ignorar parse inválido silenciosamente
            }
        }
        $records = $query->orderByDesc('occurred_at')->paginate(25)->appends([
            'chat_id' => $chatId,
            'from' => $from,
            'to' => $to,
        ]);
        $chats = OpenAIChat::where('user_id', Auth::id())->orderBy('title')->get(['id','title','code']);
        $selectedChat = null;
        if ($chatId > 0) {
            $selectedChat = $chats->firstWhere('id', $chatId);
        }
        return view('openai.records.index', compact('records','chats','chatId','selectedChat','from','to'));
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
