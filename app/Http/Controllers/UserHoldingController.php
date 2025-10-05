<?php
namespace App\Http\Controllers;

use App\Models\UserHolding;
use App\Models\InvestmentAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserHoldingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function parseNumber(?string $val, int $decimals = 6): float
    {
        if($val === null) return 0.0;
        $v = trim($val);
        if($v === '') return 0.0;
        // remove espaços
        $v = str_replace([' '], '', $v);
        // milhar . e decimal ,
        if(preg_match('/\d+\.\d{3}[,.]/', $v)){
            $v = str_replace('.', '', $v); // remove milhares
        }
        $v = str_replace(',', '.', $v);
        return is_numeric($v) ? (float)$v : 0.0;
    }

    public function create()
    {
        $userId = Auth::id();
        $accounts = InvestmentAccount::where('user_id', $userId)->orderBy('account_name')->get();
        $holding = new UserHolding();
        return view('portfolio.holding_form', [
            'holding' => $holding,
            'accounts' => $accounts,
            'mode' => 'create',
        ]);
    }

    public function store(Request $request)
    {
        $userId = Auth::id();
        $validated = $request->validate([
            'code' => 'required|string|max:32',
            'account_id' => 'nullable|integer|exists:investment_accounts,id',
            'quantity' => 'required',
            'avg_price' => 'required',
            'invested_value' => 'nullable',
            'current_price' => 'nullable',
            'currency' => 'nullable|string|max:8',
        ]);
        $quantity = $this->parseNumber($validated['quantity']);
        $avgPrice = $this->parseNumber($validated['avg_price']);
        $invested = array_key_exists('invested_value',$validated) && $validated['invested_value'] !== null && $validated['invested_value'] !== ''
            ? $this->parseNumber($validated['invested_value'], 2)
            : ($quantity * $avgPrice);
        $current = array_key_exists('current_price',$validated) && $validated['current_price'] !== null && $validated['current_price'] !== ''
            ? $this->parseNumber($validated['current_price'])
            : null;
        // Evitar duplicidade pelo unique (user_id, code, account_id)
        $exists = UserHolding::where('user_id',$userId)
            ->where('code', strtoupper(trim($validated['code'])))
            ->where('account_id', $validated['account_id'] ?? null)
            ->exists();
        if($exists){
            return back()->withErrors(['code'=>'Já existe holding para este código e conta.'])->withInput();
        }
        $h = UserHolding::create([
            'user_id' => $userId,
            'account_id' => $validated['account_id'] ?? null,
            'code' => strtoupper(trim($validated['code'])),
            'quantity' => $quantity,
            'avg_price' => $avgPrice,
            'invested_value' => $invested,
            'current_price' => $current,
            'currency' => $validated['currency'] ?? null,
        ]);
        return redirect()->route('openai.portfolio.index')->with('success','Posição criada.');
    }

    public function edit(UserHolding $holding)
    {
        if($holding->user_id !== Auth::id()) abort(403);
        $accounts = InvestmentAccount::where('user_id', Auth::id())->orderBy('account_name')->get();
        return view('portfolio.holding_form', [
            'holding'=>$holding,
            'accounts'=>$accounts,
            'mode'=>'edit',
        ]);
    }

    public function update(Request $request, UserHolding $holding)
    {
        if($holding->user_id !== Auth::id()) abort(403);
        $validated = $request->validate([
            'code' => 'required|string|max:32',
            'account_id' => 'nullable|integer|exists:investment_accounts,id',
            'quantity' => 'required',
            'avg_price' => 'required',
            'invested_value' => 'nullable',
            'current_price' => 'nullable',
            'currency' => 'nullable|string|max:8',
        ]);
        $quantity = $this->parseNumber($validated['quantity']);
        $avgPrice = $this->parseNumber($validated['avg_price']);
        $invested = array_key_exists('invested_value',$validated) && $validated['invested_value'] !== null && $validated['invested_value'] !== ''
            ? $this->parseNumber($validated['invested_value'], 2)
            : ($quantity * $avgPrice);
        $current = array_key_exists('current_price',$validated) && $validated['current_price'] !== null && $validated['current_price'] !== ''
            ? $this->parseNumber($validated['current_price'])
            : null;
        // verificar duplicidade
        $duplicate = UserHolding::where('user_id', Auth::id())
            ->where('code', strtoupper(trim($validated['code'])))
            ->where('account_id', $validated['account_id'] ?? null)
            ->where('id','<>',$holding->id)
            ->exists();
        if($duplicate){
            return back()->withErrors(['code'=>'Já existe outra holding para este código e conta.'])->withInput();
        }
        $holding->update([
            'code' => strtoupper(trim($validated['code'])),
            'account_id' => $validated['account_id'] ?? null,
            'quantity' => $quantity,
            'avg_price' => $avgPrice,
            'invested_value' => $invested,
            'current_price' => $current,
            'currency' => $validated['currency'] ?? null,
        ]);
        return redirect()->route('openai.portfolio.index')->with('success','Posição atualizada.');
    }

    public function destroy(UserHolding $holding)
    {
        if($holding->user_id !== Auth::id()) abort(403);
        $holding->delete();
        return redirect()->route('openai.portfolio.index')->with('success','Posição removida.');
    }

    public function importForm()
    {
        $accounts = InvestmentAccount::where('user_id', Auth::id())->orderBy('account_name')->get();
        return view('portfolio.holding_import', [
            'accounts'=>$accounts,
        ]);
    }

    protected function normalizeHeader(string $h): string
    {
        $h = trim(mb_strtolower($h));
        $h = str_replace(['  ','   '],' ',$h);
        $map = [
            'ação'=> 'ativo','ação'=>'ativo','papel'=>'code','ticker'=>'code','symbol'=>'code','ativo'=>'code','código'=>'code','codigo'=>'code',
            'quantidade'=>'quantity','qtd'=>'quantity','qde'=>'quantity','shares'=>'quantity','posicao'=>'quantity','posição'=>'quantity',
            'preço médio'=>'avg_price','preco medio'=>'avg_price','preço medio'=>'avg_price','preco médio'=>'avg_price','pm'=>'avg_price','avg price'=>'avg_price','avg_price'=>'avg_price',
            'investido'=>'invested_value','valor investido'=>'invested_value','total investido'=>'invested_value','invested'=>'invested_value','total'=>'invested_value',
            'cotação'=>'current_price','cotacao'=>'current_price','price'=>'current_price','current price'=>'current_price','current_price'=>'current_price',
            'moeda'=>'currency','currency'=>'currency'
        ];
        $h = str_replace([';','#','\t'],' ',$h);
        $h = preg_replace('/\s+/',' ',$h);
        return $map[$h] ?? $h;
    }

    /**
     * Parser específico para formato exportado da Avenue:
     * - Delimitador TAB
     * - Células podem conter múltiplas linhas dentro de aspas
     * - Colunas observadas: Ativo | Tipo | Cotação | Quantidade | Preço medio | Lucro ou prejuízo
     * - A coluna Ativo possui nome + ticker (última linha é o código)
     * - A coluna Preço medio é usada como avg_price
     * - Quantidade diretamente
     * - Investido não presente puro -> usamos qty * avg
     */
    protected function parseAvenue(string $raw): array
    {
        // Detecta padrão do header com TAB
        if(!preg_match('/Ativo\tTipo\tCotação\tQuantidade/i', $raw)) return [];
        // Normaliza quebras
        $raw = str_replace(["\r\n","\r"], "\n", $raw);
        $rows = [];
        $cell = '';
        $row = [];
        $inQuotes = false;
        $len = strlen($raw);
        for($i=0;$i<$len;$i++){
            $ch = $raw[$i];
            if($ch === '"'){
                $next = $i+1 < $len ? $raw[$i+1] : null;
                if($inQuotes && $next === '"'){ // escaped ""
                    $cell .= '"';
                    $i++;
                } else {
                    $inQuotes = !$inQuotes;
                }
                continue;
            }
            if($ch === "\t" && !$inQuotes){
                $row[] = $cell; $cell=''; continue;
            }
            if($ch === "\n" && !$inQuotes){
                $row[] = $cell; $cell='';
                // Commit row if has any non-empty cell
                if(count(array_filter($row, fn($v)=>trim($v) !== ''))>0){
                    $rows[] = $row;
                }
                $row = [];
                continue;
            }
            $cell .= $ch;
        }
        // Final cell/row
        if($cell !== '' || $row){ $row[]=$cell; if(count(array_filter($row, fn($v)=>trim($v) !== ''))>0) $rows[]=$row; }
        if(empty($rows)) return [];
        $header = $rows[0];
        // Verifica colunas esperadas mínimas
        if(count($header) < 5) return [];
        $data = [];
        for($r=1; $r<count($rows); $r++){
            $cols = $rows[$r];
            if(count($cols) < 5) continue; // linha incompleta
            [$colAtivo, $colTipo, $colCotacao, $colQuantidade, $colPrecoMedio] = [$cols[0], $cols[1], $cols[2], $cols[3], $cols[4]];
            $ativoLines = array_values(array_filter(array_map('trim', preg_split('/\n+/', (string)$colAtivo))));
            if(empty($ativoLines)) continue;
            $code = strtoupper(end($ativoLines));
            if(!preg_match('/^[A-Z0-9\.\-]{1,10}$/',$code)) continue; // ticker inválido
            $qtyRaw = trim($colQuantidade);
            // Corrigir vírgula decimal ou ponto milhares (ex: 600.2 ou 1.030)
            $qty = $this->parseNumber($qtyRaw);
            if($qty <= 0) continue;
            $pmRaw = trim($colPrecoMedio);
            $pmRaw = preg_replace('/U\$\s*/i','',$pmRaw);
            $pmLines = array_values(array_filter(array_map('trim', preg_split('/\n+/', $pmRaw))));
            $pmCandidate = $pmLines[0] ?? $pmRaw;
            $avg = $this->parseNumber($pmCandidate, 6);
            if($avg <= 0){
                // tentar extrair primeiro número
                if(preg_match('/([0-9]{1,3}(?:\.[0-9]{3})*,[0-9]{2})/',$pmRaw,$m)){
                    $avg = $this->parseNumber($m[1],6);
                }
            }
            if($avg <= 0) continue;
            // Extrair current_price da coluna Cotação (primeira linha com número)
            $cotRaw = trim($colCotacao);
            $cotRaw = preg_replace('/U\$\s*/i','',$cotRaw);
            $cotLines = array_values(array_filter(array_map('trim', preg_split('/\n+/', $cotRaw))));
            $cpCandidate = null;
            foreach($cotLines as $cl){
                if(preg_match('/([0-9]{1,3}(?:\.[0-9]{3})*,[0-9]{2})/',$cl,$m)){
                    $cpCandidate = $m[1];
                    break;
                }
            }
            $currentPrice = $cpCandidate ? $this->parseNumber($cpCandidate,6) : null;
            $data[] = [
                'code' => $code,
                'quantity' => $qty,
                'avg_price' => $avg,
                'invested_value' => $qty * $avg,
                'currency' => 'USD',
                'current_price' => $currentPrice
            ];
        }
        return $data;
    }

    public function importStore(Request $request)
    {
        // Permitimos agora duas formas: upload de arquivo ou textarea csv_raw.
        $request->validate([
            'csv' => 'nullable|file|max:4096',
            'csv_raw' => 'nullable|string',
            'account_id' => 'nullable|integer|exists:investment_accounts,id',
            'create_account_name' => 'nullable|string|max:100',
            'create_account_broker' => 'nullable|string|max:100',
            'mode_merge' => 'nullable|in:replace,sum',
        ]);
        if(!$request->file('csv') && !$request->filled('csv_raw')){
            return back()->withErrors(['csv'=>'Envie um arquivo CSV ou cole o conteúdo no campo de texto.'])->withInput();
        }
        $userId = Auth::id();
        $accountId = $request->input('account_id');
        if(!$accountId && $request->filled('create_account_name')){
            $acc = InvestmentAccount::create([
                'user_id' => $userId,
                'account_name' => $request->input('create_account_name'),
                'broker' => $request->input('create_account_broker'),
                'date' => now()->toDateString(),
                'total_invested' => 0,
            ]);
            $accountId = $acc->id;
        }
        $modeMerge = $request->input('mode_merge','replace');
        $raw = '';
        if($request->file('csv')){
            $file = $request->file('csv');
            $raw = file_get_contents($file->getRealPath());
        } else {
            $raw = (string)$request->input('csv_raw');
        }
        if(!$raw){ return back()->withErrors(['csv'=>'Conteúdo vazio'])->withInput(); }
        // Normalizar encoding
        $enc = mb_detect_encoding($raw, ['UTF-8','ISO-8859-1','WINDOWS-1252'], true) ?: 'UTF-8';
        if($enc !== 'UTF-8') $raw = mb_convert_encoding($raw,'UTF-8',$enc);
        // Detectar delimitador predominante (; ou ,)
        $firstLines = implode("\n", array_slice(preg_split('/\r?\n/',$raw),0,5));
        $semi = substr_count($firstLines,';'); $comma = substr_count($firstLines,',');
        $delim = $semi > $comma ? ';' : ',';
        $lines = preg_split('/\r?\n/',$raw);
        $headers = [];
        $dataRows = [];
        // Primeiro tenta parser Avenue específico (multi-linha/tab)
        $avenueRows = $this->parseAvenue($raw);
        if(!empty($avenueRows)){
            foreach($avenueRows as $r){
                $dataRows[] = [
                    'code' => $r['code'],
                    'quantity' => $r['quantity'],
                    'avg_price' => $r['avg_price'],
                    'invested_value' => $r['invested_value'],
                    'currency' => $r['currency'],
                    'current_price' => $r['current_price'] ?? null,
                ];
            }
        } else {
            foreach($lines as $ln){
                if(trim($ln)==='') continue;
                $cols = str_getcsv($ln, $delim);
                $lowerJoined = mb_strtolower(implode(' ', $cols));
                if(empty($headers) && (str_contains($lowerJoined,'preco') || str_contains($lowerJoined,'price') || str_contains($lowerJoined,'quant') || str_contains($lowerJoined,'pm') || str_contains($lowerJoined,'avg'))){
                    $headers = array_map(fn($h)=> $this->normalizeHeader($h), $cols);
                    continue;
                }
                if(!empty($headers)){
                    if(count($cols) !== count($headers)) continue;
                    $rowAssoc = [];
                    foreach($headers as $i=>$h){ $rowAssoc[$h] = $cols[$i] ?? null; }
                    $dataRows[] = $rowAssoc;
                }
            }
            if(empty($headers)){
                return back()->withErrors(['csv'=>'Não foi possível detectar cabeçalho. Verifique se inclui linha com colunas (ex: Código;Quantidade;Preço Médio;Investido).'])->withInput();
            }
        }
        $ins = 0; $upd = 0; $skip = 0; $errors = [];
        foreach($dataRows as $r){
            $codeRaw = $r['code'] ?? $r['ativo'] ?? null;
            if(!$codeRaw){ $skip++; continue; }
            $code = strtoupper(trim($codeRaw));
            if($code==='') { $skip++; continue; }
            $qty = $this->parseNumber($r['quantity'] ?? '0');
            if(abs($qty) < 1e-12){ $skip++; continue; }
            $avg = $this->parseNumber($r['avg_price'] ?? ($r['preco'] ?? $r['price'] ?? '0'));
            if($avg <= 0){ $skip++; continue; }
            $invested = isset($r['invested_value']) && $r['invested_value'] !== '' ? $this->parseNumber($r['invested_value'],2) : ($qty * $avg);
            $curr = $r['currency'] ?? null;
            $curPrice = isset($r['current_price']) && $r['current_price'] !== '' ? $this->parseNumber((string)$r['current_price']) : null;
            $hold = UserHolding::where('user_id',$userId)->where('code',$code)->where('account_id',$accountId)->first();
            if($hold){
                if($modeMerge === 'sum'){
                    $newQty = $hold->quantity + $qty;
                    $totalCost = ($hold->avg_price * $hold->quantity) + ($avg * $qty);
                    $newAvg = $newQty>0 ? ($totalCost / $newQty) : 0;
                    $hold->quantity = $newQty;
                    $hold->avg_price = $newAvg;
                    $hold->invested_value = $hold->invested_value + $invested;
                    if($curr) $hold->currency = $curr;
                    if($curPrice !== null) $hold->current_price = $curPrice;
                    $hold->save();
                } else { // replace
                    $hold->update([
                        'quantity'=>$qty,
                        'avg_price'=>$avg,
                        'invested_value'=>$invested,
                        'currency'=>$curr ?: $hold->currency,
                        'current_price'=>$curPrice !== null ? $curPrice : $hold->current_price,
                    ]);
                }
                $upd++;
            } else {
                try {
                    UserHolding::create([
                        'user_id'=>$userId,
                        'account_id'=>$accountId,
                        'code'=>$code,
                        'quantity'=>$qty,
                        'avg_price'=>$avg,
                        'invested_value'=>$invested,
                        'current_price'=>$curPrice,
                        'currency'=>$curr,
                    ]);
                    $ins++;
                } catch(\Throwable $e){
                    $errors[] = $code.': '.$e->getMessage();
                }
            }
        }
        $msg = "Importação concluída: {$ins} inseridos, {$upd} atualizados, {$skip} ignorados";
        if($errors){ $msg .= '. Erros: '.implode('; ',$errors); }
        return redirect()->route('openai.portfolio.index')->with('success',$msg);
    }
}
