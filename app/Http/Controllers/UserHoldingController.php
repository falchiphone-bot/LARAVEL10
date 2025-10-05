<?php
namespace App\Http\Controllers;

use App\Models\UserHolding;
use App\Models\InvestmentAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;

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

    public function bulkDestroy(Request $request)
    {
        $userId = Auth::id();
        // Confirmação simples via parâmetro (evita deleção acidental por CSRF replay)
        if($request->input('confirm') !== 'yes'){
            return back()->withErrors(['confirm'=>'Confirmação ausente.']);
        }
        $count = UserHolding::where('user_id', $userId)->count();
        UserHolding::where('user_id', $userId)->delete(); // soft delete
        return redirect()->route('openai.portfolio.index')->with('success', "Removidas (soft) {$count} posições.");
    }

    public function exportCsv(Request $request)
    {
        $userId = Auth::id();
        $rows = UserHolding::where('user_id',$userId)->orderBy('code')->get();
        $lines = [];
        $lines[] = 'Codigo;Conta;Corretora;Quantidade;PrecoMedio;Investido;CurrentPrice;Moeda;AtualizadoEm';
        foreach($rows as $h){
            $lines[] = implode(';', [
                $h->code,
                $h->account_id,
                $h->account?->broker,
                number_format((float)$h->quantity,6,',','.'),
                number_format((float)$h->avg_price,6,',','.'),
                number_format((float)$h->invested_value,2,',','.'),
                $h->current_price !== null ? number_format((float)$h->current_price,6,',','.') : '',
                $h->currency ?? '',
                $h->updated_at?->format('Y-m-d H:i:s')
            ]);
        }
        $content = implode("\n", $lines);
        $filename = 'holdings_export_'.date('Ymd_His').'.csv';
        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"'
        ]);
    }

    public function exportXlsx(Request $request)
    {
        $userId = Auth::id();
        $rows = UserHolding::with('account')->where('user_id',$userId)->orderBy('code')->get();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headers = ['Codigo','Conta','Corretora','Quantidade','PrecoMedio','Investido','CurrentPrice','Moeda','AtualizadoEm'];
        $col = 1; foreach($headers as $h){ $sheet->setCellValueByColumnAndRow($col,1,$h); $col++; }
        $rIdx = 2;
        foreach($rows as $h){
            $sheet->setCellValueExplicit("A{$rIdx}", $h->code, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("B{$rIdx}", $h->account_id);
            $sheet->setCellValue("C{$rIdx}", $h->account?->broker);
            $sheet->setCellValue("D{$rIdx}", (float)$h->quantity);
            $sheet->setCellValue("E{$rIdx}", (float)$h->avg_price);
            $sheet->setCellValue("F{$rIdx}", (float)$h->invested_value);
            if($h->current_price !== null) $sheet->setCellValue("G{$rIdx}", (float)$h->current_price);
            $sheet->setCellValue("H{$rIdx}", $h->currency);
            $sheet->setCellValue("I{$rIdx}", $h->updated_at?->format('Y-m-d H:i:s'));
            $rIdx++;
        }
        foreach(range('A','I') as $c){ $sheet->getColumnDimension($c)->setAutoSize(true); }
        $fname = 'holdings_export_'.date('Ymd_His').'.xlsx';
        $tmp = tempnam(sys_get_temp_dir(),'xls');
        (new Xlsx($spreadsheet))->save($tmp);
        return response()->download($tmp, $fname)->deleteFileAfterSend(true);
    }

    public function templateCsv()
    {
        $lines = [
            'Codigo;Quantidade;Preço Médio;Investido;Moeda',
            'AAPL;10;150,25;1502,50;USD',
            'MSFT;5;320,10;;USD',
            'KO;51;69,12;;USD',
        ];
        $content = implode("\n", $lines);
        return response($content,200,[
            'Content-Type'=>'text/csv; charset=UTF-8',
            'Content-Disposition'=>'attachment; filename="holdings_template.csv"'
        ]);
    }

    public function templateCsvByAccount($accountId)
    {
        $userId = Auth::id();
        $rows = UserHolding::where('user_id',$userId)->where('account_id',$accountId)->orderBy('code')->get();
        $lines = ['Codigo;Quantidade;Preço Médio;Investido;Moeda'];
        foreach($rows as $h){
            $lines[] = implode(';', [
                $h->code,
                number_format((float)$h->quantity,6,',','.'),
                number_format((float)$h->avg_price,6,',','.'),
                number_format((float)$h->invested_value,2,',','.'),
                $h->currency ?? ''
            ]);
        }
        $content = implode("\n", $lines);
        return response($content,200,[
            'Content-Type'=>'text/csv; charset=UTF-8',
            'Content-Disposition'=>'attachment; filename="holdings_template_conta_'.$accountId.'.csv"'
        ]);
    }

    public function reimportRedirect(Request $request)
    {
        // Soft delete tudo e redireciona para import
        $userId = Auth::id();
        UserHolding::where('user_id',$userId)->delete();
        return redirect()->route('holdings.import.form')->with('success','Posições limpas (soft delete). Agora importe o novo arquivo.');
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

    /**
     * Fallback para conteúdo colado onde tabs foram perdidos e cada registro aparece em blocos:
     * " (linha isolada)
     * Nome da Empresa
     * TICKER" (fecha aspas)
     * Ações
     * "U$ 145,54
     * U$ 1,08 (0,75%)"  (cotação + var intraday)
     * 1                  (quantidade)
     * U$ 144,35          (preço médio)
     * "U$ 145,54         (valor atual)
     * U$ 1,19 (0,82%)"   (lucro/perda) – ignorado
     */
    protected function parseAvenueBlocks(string $raw): array
    {
        // heurística rápida: precisa ter linhas com apenas " e linha de cabeçalho Ativo Tipo Cotação
        if(!preg_match('/Ativo\s+Tipo\s+Cot[aã]ção/i', $raw)) return [];
        $lines = preg_split('/\r?\n/', $raw);
        $clean = [];
        foreach($lines as $ln){ $clean[] = rtrim($ln); }
        $out = [];
        $n = count($clean);
        for($i=0;$i<$n;$i++){
            if(trim($clean[$i]) === '"'){
                // bloco potencial
                $name = null; $ticker = null; $cotLines = []; $qty = null; $avg = null; $currentPrice = null;
                $j = $i+1;
                if($j < $n) { $name = trim($clean[$j]); $j++; }
                if($j < $n) {
                    $tickerLine = trim($clean[$j]); $j++;
                    if(str_ends_with($tickerLine, '"')){
                        $ticker = strtoupper(trim(substr($tickerLine,0,-1)));
                    } else {
                        // formato inesperado; aborta este bloco
                        continue;
                    }
                }
                // Próxima linha deve ser 'Ações' (ou outro tipo). Avança.
                if($j < $n && preg_match('/Ações|Ac[oõ]es/i', $clean[$j])){ $j++; }
                // Cotação dentro de aspas (2 linhas + fecha)
                if($j < $n && trim($clean[$j]) === '"'){
                    // formato diferente, ignora
                }
                if($j < $n && str_starts_with(trim($clean[$j]), '"')){
                    // linha inicial com aspas e conteúdo ou apenas abre aspas?
                    $firstCot = ltrim(trim($clean[$j]), '"');
                    $cotLines[] = $firstCot;
                    $j++;
                    // adicionar até achar linha que fecha aspas
                    while($j < $n){
                        $l = trim($clean[$j]);
                        if(str_ends_with($l, '"')){
                            $cotLines[] = substr($l,0,-1);
                            $j++; break;
                        } else {
                            $cotLines[] = $l; $j++;
                        }
                    }
                }
                // Quantidade
                if($j < $n){ $qtyLine = trim($clean[$j]); $j++; }
                else { continue; }
                $qtyNum = $this->parseNumber($qtyLine ?? '0');
                // Preço médio (linha começando com U$)
                if($j < $n){ $avgLine = trim($clean[$j]); $j++; }
                else { continue; }
                $avgLineNorm = preg_replace('/U\$\s*/i','',$avgLine);
                $avgNum = $this->parseNumber($avgLineNorm,6);
                // Valor atual + lucro/perda em bloco de aspas (opcional)
                if($j < $n && str_starts_with(trim($clean[$j]), '"')){
                    $j++; // ignora bloco inteiro
                    while($j < $n){
                        $l = trim($clean[$j]);
                        if(str_ends_with($l,'"')){ $j++; break; }
                        $j++;
                    }
                }
                // current_price = primeira linha numérica da cotação
                $cpCandidate = null;
                foreach($cotLines as $cl){
                    if(preg_match('/([0-9]{1,3}(?:\.[0-9]{3})*,[0-9]{2})/',$cl,$m)) { $cpCandidate = $m[1]; break; }
                }
                $cpNum = $cpCandidate ? $this->parseNumber($cpCandidate,6) : null;
                if($ticker && $qtyNum>0 && $avgNum>0){
                    if(preg_match('/^[A-Z0-9.\-]{1,12}$/',$ticker)){
                        $out[] = [
                            'code'=>$ticker,
                            'quantity'=>$qtyNum,
                            'avg_price'=>$avgNum,
                            'invested_value'=>$qtyNum*$avgNum,
                            'current_price'=>$cpNum,
                            'currency'=>'USD'
                        ];
                    }
                }
                // mover i para antes de j-1
                $i = $j-1;
            }
        }
        return $out;
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
        $dataRows = [];
        $dbgAvenueCount = 0; $dbgAvenueBlocksCount = 0; // contadores de diagnósticos
        $raw = '';
        $isExcel = false;
    if($request->file('csv')){
            $file = $request->file('csv');
            $ext = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
            if(in_array($ext, ['xlsx','xls'])){
                $isExcel = true;
                try {
                    $spreadsheet = IOFactory::load($file->getRealPath());
                    $sheet = $spreadsheet->getActiveSheet();
                    $highestCol = $sheet->getHighestDataColumn();
                    $highestRow = $sheet->getHighestDataRow();
                    $colCount = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);
                    $headerRowIndex = 1;
                    $headerMap = [];
                    // Primeiro passa: linha 1
                    for($c=1;$c<=$colCount;$c++){
                        $valRaw = (string)$sheet->getCellByColumnAndRow($c,1)->getValue();
                        $val = trim($valRaw);
                        if($val!==''){
                            $headerMap[$c] = mb_strtolower($val);
                        }
                    }
                    // Heurística: se primeira célula é 'tabela' e linha 2 contém cabeçalhos reconhecíveis
                    if(isset($headerMap[1]) && str_starts_with($headerMap[1], 'tabela') && $highestRow >= 2){
                        $possible = [];
                        for($c=1;$c<=$colCount;$c++){
                            $v2 = trim((string)$sheet->getCellByColumnAndRow($c,2)->getValue());
                            if($v2!=='') $possible[$c] = mb_strtolower($v2);
                        }
                        $vals = array_values($possible);
                        $matchKeywords = 0;
                        foreach(['ativo','cotação','cotacao','quantidade','preço','preco'] as $kw){
                            foreach($vals as $vv){ if(str_starts_with($vv,$kw)){ $matchKeywords++; break; } }
                        }
                        if($matchKeywords >= 2){
                            // Reinterpretar cabeçalho como linha 2
                            $headerMap = $possible;
                            $headerRowIndex = 2;
                        }
                    }
                    // Se ainda só temos 1 coluna de cabeçalho e ela não começa com 'ativo', tentar segunda linha mesmo sem 'tabela'
                    if(count($headerMap) <= 1 && $highestRow >=2 && $headerRowIndex===1){
                        $possible2 = [];
                        for($c=1;$c<=$colCount;$c++){
                            $v2 = trim((string)$sheet->getCellByColumnAndRow($c,2)->getValue());
                            if($v2!=='') $possible2[$c] = mb_strtolower($v2);
                        }
                        if(count($possible2) > count($headerMap)){
                            $headerMap = $possible2; $headerRowIndex = 2;
                        }
                    }
                    // Detectar formato Avenue
                    $isAvenueSheet = false;
                    $valuesLower = array_values($headerMap);
                    if(!empty($valuesLower)){
                        if(str_starts_with($valuesLower[0],'ativo') && (in_array('quantidade',$valuesLower) || in_array('qtd',$valuesLower))){
                            $isAvenueSheet = true;
                        }
                    }
                    if($isAvenueSheet){
                        $dbgSkipAvenueCode = 0; $dbgSkipAvenueQty = 0; $dbgSkipAvenueAvg = 0;
                        $dataStart = $headerRowIndex + 1;
                        for($r=$dataStart; $r <= $highestRow; $r++){
                            $ativoCell = (string)$sheet->getCellByColumnAndRow(1,$r)->getValue();
                            if(trim($ativoCell)==='') continue;
                            // Quebras de linha dentro do Excel podem ser "\n" ou "\r\n"
                            $ativoLines = array_values(array_filter(array_map('trim', preg_split('/\r?\n/',$ativoCell))));
                            if(empty($ativoLines)) continue;
                            $code = strtoupper(end($ativoLines));
                            // Sanitiza removendo caracteres estranhos (aspas, espaços não quebra, etc.)
                            $code = preg_replace('/[^A-Z0-9.\-]/','', $code);
                            if($code === '') { $dbgSkipAvenueCode++; continue; }
                            // Coluna Cotação (3) -> procurar primeiro número
                            $cotCell = (string)$sheet->getCellByColumnAndRow(3,$r)->getValue();
                            $cotLines = array_values(array_filter(array_map('trim', preg_split('/\r?\n/',$cotCell))));
                            $cpCandidate = null;
                            foreach($cotLines as $cl){
                                if(preg_match('/([0-9]{1,3}(?:\.[0-9]{3})*,[0-9]{2})/',$cl,$m)){ $cpCandidate = $m[1]; break; }
                            }
                            $currentPrice = $cpCandidate ? $this->parseNumber($cpCandidate,6) : null;
                            // Quantidade (4)
                            $qtyCell = (string)$sheet->getCellByColumnAndRow(4,$r)->getValue();
                            $qty = $this->parseNumber((string)$qtyCell);
                            if($qty <= 0) { $dbgSkipAvenueQty++; continue; }
                            // Preço Médio (5)
                            $pmCell = (string)$sheet->getCellByColumnAndRow(5,$r)->getValue();
                            $pmCellNorm = preg_replace('/U\$\s*/i','',$pmCell);
                            $pmLines = array_values(array_filter(array_map('trim', preg_split('/\r?\n/',$pmCellNorm))));
                            $pmCandidate = $pmLines[0] ?? $pmCellNorm;
                            $avg = $this->parseNumber($pmCandidate,6);
                            if($avg <= 0) { $dbgSkipAvenueAvg++; continue; }
                            $dataRows[] = [
                                'code'=>$code,
                                'quantity'=>$qty,
                                'avg_price'=>$avg,
                                'invested_value'=>$qty*$avg,
                                'current_price'=>$currentPrice,
                                'currency'=>'USD'
                            ];
                        }
                        $dbgAvenueCount = count($dataRows);
                        // Anexa info de skip internos (apenas se nenhum inserido nessa fase)
                        if($dbgAvenueCount === 0 && ($dbgSkipAvenueCode||$dbgSkipAvenueQty||$dbgSkipAvenueAvg)){
                            // Guardar como pseudo-linha para relatório posterior (não influencia import)
                            $dataRows[] = ['__dbg_skip_code'=>$dbgSkipAvenueCode,'__dbg_skip_qty'=>$dbgSkipAvenueQty,'__dbg_skip_avg'=>$dbgSkipAvenueAvg];
                        }
                    } else {
                        // Fallback genérico: mapear colunas por nome normalizado (reutilizando normalizeHeader)
                        $normMap = [];
                        foreach($headerMap as $colIdx=>$name){
                            $normMap[$colIdx] = $this->normalizeHeader($name);
                        }
                        $dataStart = $headerRowIndex + 1;
                        for($r=$dataStart; $r <= $highestRow; $r++){
                            $rowAssoc = [];
                            $empty = 0;
                            for($c=1;$c<=$colCount;$c++){
                                $val = (string)$sheet->getCellByColumnAndRow($c,$r)->getValue();
                                if(trim($val)==='') $empty++;
                                if(!isset($normMap[$c])) continue;
                                $rowAssoc[$normMap[$c]] = $val;
                            }
                            if($empty >= $colCount) continue;
                            $dataRows[] = $rowAssoc;
                        }
                        // Caso extremo: apenas uma coluna (ex: 'ativo') mas sem demais colunas necessárias
                        if(count($normMap) === 1 && isset($normMap[array_key_first($normMap)]) && $normMap[array_key_first($normMap)] === 'code'){
                            // Vamos tentar extrair de blocos se possível (cada célula contém bloco multi-linha)
                            $converted = [];
                            foreach($dataRows as $one){
                                $block = (string)($one['code'] ?? '');
                                $lines = array_values(array_filter(array_map('trim', preg_split('/\r?\n/',$block))));
                                if(count($lines) < 2) continue;
                                $ticker = strtoupper(end($lines));
                                $ticker = preg_replace('/[^A-Z0-9.\-]/','',$ticker);
                                if($ticker==='') continue;
                                // Sem qty/avg não há como criar holding real
                                // Apenas registra debug para orientar usuário
                            }
                            if(empty($converted)){
                                return back()->withErrors(['csv'=>'Arquivo Excel contém somente uma coluna (ATIVO) sem Quantidade/Preço Médio. Exporte novamente incluindo todas as colunas (Ativo, Tipo, Cotação, Quantidade, Preço médio, Lucro/Prejuízo) ou use o CSV original.'])->withInput();
                            }
                        }
                    }
                } catch(\Throwable $e){
                    return back()->withErrors(['csv'=>'Falha ao ler Excel: '.$e->getMessage()])->withInput();
                }
            } else {
                $raw = file_get_contents($file->getRealPath());
            }
        } else {
            $raw = (string)$request->input('csv_raw');
        }
        if(!$isExcel && !$raw){ return back()->withErrors(['csv'=>'Conteúdo vazio'])->withInput(); }
        // Normalizar encoding
        if(!$isExcel){
            $enc = mb_detect_encoding($raw, ['UTF-8','ISO-8859-1','WINDOWS-1252'], true) ?: 'UTF-8';
            if($enc !== 'UTF-8') $raw = mb_convert_encoding($raw,'UTF-8',$enc);
            // Detectar delimitador predominante (; ou ,)
            $firstLines = implode("\n", array_slice(preg_split('/\r?\n/',$raw),0,5));
            $semi = substr_count($firstLines,';'); $comma = substr_count($firstLines,',');
            $delim = $semi > $comma ? ';' : ',';
            $lines = preg_split('/\r?\n/',$raw);
            $headers = [];
            // Primeiro tenta parser Avenue específico (multi-linha/tab)
            $avenueRows = $this->parseAvenue($raw);
            if(!empty($avenueRows)){
                $dbgAvenueCount = count($avenueRows);
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
            } elseif($avenueRows = $this->parseAvenueBlocks($raw)) {
                $dbgAvenueBlocksCount = count($avenueRows);
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
        }
        $ins = 0; $upd = 0; $skip = 0; $errors = [];
        $dbgExtra = '';
        $debugSkipped = [];
        $rowSeq = 0;
        foreach($dataRows as $r){
            if(isset($r['__dbg_skip_code'])){ // linha de debug artificial, extrair e continuar
                $dbgExtra = " DBG(code={$r['__dbg_skip_code']},qty={$r['__dbg_skip_qty']},avg={$r['__dbg_skip_avg']})";
                continue;
            }
            $rowSeq++;
            $codeRaw = $r['code'] ?? $r['ativo'] ?? null;
            if(!$codeRaw){ $skip++; $debugSkipped[] = ['row'=>$rowSeq,'reason'=>'missing_code','raw'=>$r]; continue; }
            $code = strtoupper(trim($codeRaw));
            $code = preg_replace('/[^A-Z0-9.\-]/','', $code);
            if($code==='') { $skip++; $debugSkipped[] = ['row'=>$rowSeq,'reason'=>'empty_code_after_sanitize','raw'=>$r]; continue; }
            $qty = $this->parseNumber($r['quantity'] ?? '0');
            if(abs($qty) < 1e-12){ $skip++; $debugSkipped[] = ['row'=>$rowSeq,'reason'=>'zero_quantity','code'=>$code,'raw_qty'=>($r['quantity'] ?? null)]; continue; }
            $avg = $this->parseNumber($r['avg_price'] ?? ($r['preco'] ?? $r['price'] ?? '0'));
            if($avg <= 0){ $skip++; $debugSkipped[] = ['row'=>$rowSeq,'reason'=>'invalid_avg_price','code'=>$code,'raw_avg'=>($r['avg_price'] ?? $r['preco'] ?? $r['price'] ?? null)]; continue; }
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
    $msg = "Importação concluída: {$ins} inseridos, {$upd} atualizados, {$skip} ignorados".$dbgExtra;
        if($dbgAvenueCount>0 || $dbgAvenueBlocksCount>0){
            $det = [];
            if($dbgAvenueCount>0) $det[] = "AvenueTab={$dbgAvenueCount}";
            if($dbgAvenueBlocksCount>0) $det[] = "AvenueBlocks={$dbgAvenueBlocksCount}";
            if($det) $msg .= ' ['.implode(', ',$det).']';
        }
    if($errors){ $msg .= '. Erros: '.implode('; ',$errors); }
        // Logging detalhado somente se nada entrou/atualizou
        if($ins===0 && $upd===0){
            // limitar tamanho para não explodir log
            $sample = array_slice($debugSkipped,0,50);
            Log::info('Holdings import sem inserções/atualizações', [
                'user_id'=>$userId,
                'account_id'=>$accountId,
                'rows_total'=>count($dataRows),
                'skipped'=>$skip,
                'avenue_tab'=>$dbgAvenueCount,
                'avenue_blocks'=>$dbgAvenueBlocksCount,
                'dbg_extra'=>$dbgExtra,
                'sample_skipped'=>$sample,
            ]);
        }
        return redirect()->route('openai.portfolio.index')->with('success',$msg);
    }
}
