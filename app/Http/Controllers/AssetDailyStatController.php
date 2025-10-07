<?php

namespace App\Http\Controllers;

use App\Models\AssetDailyStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\OpenAIChat;
use App\Models\OpenAIChatRecord;

class AssetDailyStatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Se você tiver Spatie Permissions, ajuste os nomes como preferir
        $this->middleware('permission:ASSET STATS - LISTAR')->only(['index']);
        $this->middleware('permission:ASSET STATS - CRIAR')->only(['create','store','importForm','importStore']);
        $this->middleware('permission:ASSET STATS - EDITAR')->only(['edit','update','refreshClose','recomputeAccuracy','fillCloseFromRecords','fillCloseFromRecordsBulk']);
        $this->middleware('permission:ASSET STATS - EXCLUIR')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $symbol = strtoupper(trim((string)$request->input('symbol')));
        $sort = (string)$request->input('sort', 'date');
    $dir = strtolower((string)$request->input('dir', 'asc'));
        $dateStartRaw = trim((string)$request->input('date_start'));
        $dateEndRaw = trim((string)$request->input('date_end'));
        $accFilter = trim((string)$request->input('acc', '')); // '', 'ok', 'out', 'na'
    $hasClose = $request->boolean('has_close');
        $showAll = $request->boolean('all');

        $allowedSorts = ['date','symbol','acc'];
        if (!in_array($sort, $allowedSorts, true)) { $sort = 'date'; }
    if (!in_array($dir, ['asc','desc'], true)) { $dir = 'asc'; }

        $q = AssetDailyStat::query();
        if ($symbol !== '') {
            $q->where('symbol', $symbol);
        }
        if ($hasClose) {
            $q->whereNotNull('close_value');
        }
        // Filtro por acurácia
        if ($accFilter === 'ok') {
            $q->where('is_accurate', true);
        } elseif ($accFilter === 'out') {
            $q->where('is_accurate', false);
        } elseif ($accFilter === 'na') {
            $q->whereNull('is_accurate');
        }
        // Filtro por período (datas inclusivas)
        $start = $this->parseFilterDate($dateStartRaw);
        $end = $this->parseFilterDate($dateEndRaw);
        $start = $start ? $start->startOfDay() : null;
        $end = $end ? $end->endOfDay() : null;
        if ($start && $end) {
            $q->whereBetween('date', [$start, $end]);
        } elseif ($start) {
            $q->where('date', '>=', $start);
        } elseif ($end) {
            $q->where('date', '<=', $end);
        }
        // Totais para exibição (antes de paginação)
        $totalFiltered = (clone $q)->count();
        $withCloseFiltered = (clone $q)->whereNotNull('close_value')->count();

        // Ordenação primária conforme pedido e uma ordenação secundária estável
        if ($sort === 'symbol') {
            $q->orderBy('symbol', $dir)->orderBy('date', 'asc');
        } elseif ($sort === 'acc') {
            // Ordena nulos por último no ASC; no DESC nulos por primeiro
            if ($dir === 'asc') {
                $q->orderByRaw('CASE WHEN is_accurate IS NULL THEN 1 ELSE 0 END ASC')
                  ->orderBy('is_accurate', 'asc')
                  ->orderBy('date', 'asc');
            } else {
                $q->orderByRaw('CASE WHEN is_accurate IS NULL THEN 0 ELSE 1 END ASC')
                  ->orderBy('is_accurate', 'desc')
                  ->orderBy('date', 'asc');
            }
        } else { // date
            $q->orderBy('date', $dir)->orderBy('symbol', 'asc');
        }

        if ($showAll) {
            // Limite de segurança para não estourar memória: máximo 20k
            $maxRows = (int)config('openai.asset_stats_max_all', 20000);
            $collection = $q->limit($maxRows + 1)->get();
            $truncated = false;
            if ($collection->count() > $maxRows) {
                $collection = $collection->take($maxRows);
                $truncated = true;
            }
            // Criar LengthAwarePaginator fake para reutilizar a view sem alterar muito
            $stats = new \Illuminate\Pagination\LengthAwarePaginator(
                $collection,
                $collection->count(),
                $collection->count() ?: 1,
                1,
                ['path' => url()->current(), 'query' => $request->query()]
            );
        } else {
            $stats = $q->paginate(50)->appends($request->query());
            $truncated = false;
        }
        return view('asset_stats.index', [
            'stats' => $stats,
            'symbol' => $symbol,
            'sort' => $sort,
            'dir' => $dir,
            'dateStart' => $start ? $start->format('Y-m-d') : ($dateStartRaw ?: ''),
            'dateEnd' => $end ? $end->format('Y-m-d') : ($dateEndRaw ?: ''),
            'acc' => $accFilter,
            'hasClose' => $hasClose,
            'totalFiltered' => $totalFiltered,
            'withCloseFiltered' => $withCloseFiltered,
            'showAll' => $showAll,
            'truncated' => $truncated,
        ]);
    }

    public function create()
    {
        return view('asset_stats.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['symbol'] = strtoupper($data['symbol']);
        // Computa acurácia se possível
        $data['is_accurate'] = $this->computeAccuracy($data['p5'] ?? null, $data['p95'] ?? null, $data['close_value'] ?? null, $request->boolean('is_accurate', null));
        // Normaliza a data para string compatível com DATETIME2(7) (SQL Server)
        [$date, $stamp] = $this->normalizeToSqlsrvDatetime2($data['date']);
        $driver = DB::getDriverName();
        if ($driver === 'sqlsrv') {
            $ts = $this->nowStampSqlsrv();
            $affected = DB::update(
                'UPDATE [asset_daily_stats]
                 SET [mean]=?,[median]=?,[p5]=?,[p95]=?,[close_value]=?,[is_accurate]=?,[updated_at]=CAST(? AS DATETIME2(7))
                 WHERE [symbol]=? AND [date]=CAST(? AS DATETIME2(7))',
                [$data['mean'],$data['median'],$data['p5'],$data['p95'],$data['close_value'] ?? null,$data['is_accurate'],$ts,$data['symbol'],$date]
            );
            if ($affected === 0) {
                DB::insert(
                    'INSERT INTO [asset_daily_stats]([symbol],[date],[mean],[median],[p5],[p95],[close_value],[is_accurate],[created_at],[updated_at])
                     VALUES(?,CAST(? AS DATETIME2(7)),?,?,?,?,?,?,CAST(? AS DATETIME2(7)),CAST(? AS DATETIME2(7)))',
                    [$data['symbol'],$date,$data['mean'],$data['median'],$data['p5'],$data['p95'],$data['close_value'] ?? null,$data['is_accurate'],$ts,$ts]
                );
            }
        } else {
            $data['date'] = $date;
            // upsert por (symbol,date)
            $exists = AssetDailyStat::where('symbol', $data['symbol'])->where('date', $data['date'])->first();
            if ($exists) {
                $exists->update($data);
            } else {
                AssetDailyStat::create($data);
            }
        }
        return redirect()->route('asset-stats.index', ['symbol' => $data['symbol']])->with('success', 'Registro salvo.');
    }

    public function edit(AssetDailyStat $asset_stat)
    {
        return view('asset_stats.edit', ['model' => $asset_stat]);
    }

    public function update(Request $request, AssetDailyStat $asset_stat)
    {
        $data = $this->validateData($request);
        $data['symbol'] = strtoupper($data['symbol']);
        $data['is_accurate'] = $this->computeAccuracy($data['p5'] ?? null, $data['p95'] ?? null, $data['close_value'] ?? null, $request->boolean('is_accurate', null));
        // Normaliza a data para string compatível com DATETIME2(7)
        [$date, $stamp] = $this->normalizeToSqlsrvDatetime2($data['date']);
        $driver = DB::getDriverName();
        if ($driver === 'sqlsrv') {
            // Garante unicidade (symbol,date) considerando CAST
            $conflict = DB::table('asset_daily_stats')
                ->where('symbol', $data['symbol'])
                ->whereRaw('[date] = CAST(? AS DATETIME2(7))', [$date])
                ->where('id', '!=', $asset_stat->id)
                ->exists();
            if ($conflict) {
                return back()->withErrors(['date' => 'Já existe registro para este símbolo e data.'])->withInput();
            }
            $ts = $this->nowStampSqlsrv();
            DB::update(
                'UPDATE [asset_daily_stats]
                 SET [symbol]=?, [date]=CAST(? AS DATETIME2(7)), [mean]=?, [median]=?, [p5]=?, [p95]=?, [close_value]=?, [is_accurate]=?, [updated_at]=CAST(? AS DATETIME2(7))
                 WHERE [id]=?'
                , [$data['symbol'],$date,$data['mean'],$data['median'],$data['p5'],$data['p95'],$data['close_value'] ?? null,$data['is_accurate'],$ts,$asset_stat->id]
            );
        } else {
            $data['date'] = $date;
            // Garante unicidade (symbol,date) se for alterado
            $conflict = AssetDailyStat::where('symbol', $data['symbol'])
                ->where('date', $data['date'])
                ->where('id', '!=', $asset_stat->id)
                ->exists();
            if ($conflict) {
                return back()->withErrors(['date' => 'Já existe registro para este símbolo e data.'])->withInput();
            }
            $asset_stat->update($data);
        }
        return redirect()->route('asset-stats.index', ['symbol' => $data['symbol']])->with('success', 'Registro atualizado.');
    }

    public function destroy(AssetDailyStat $asset_stat)
    {
        $sym = $asset_stat->symbol;
        $asset_stat->delete();
        return redirect()->route('asset-stats.index', ['symbol'=>$sym])->with('success', 'Registro excluído.');
    }

    public function refreshClose(AssetDailyStat $asset_stat)
    {
        $this->middleware('permission:ASSET STATS - EDITAR');
        try {
            // Bloqueia atualização para datas passadas
            $d = optional($asset_stat->date)->timezone('UTC');
            if (!$d || $d->lt(\Carbon\Carbon::today('UTC'))) {
                return back()->withErrors(['close_value' => 'Atualização de Fechado permitida apenas para hoje ou datas futuras.']);
            }
            $svc = app(\App\Services\MarketDataService::class);
            $date = optional($asset_stat->date)->format('Y-m-d');
            $hq = $svc->getHistoricalQuote($asset_stat->symbol, $date);
            if (!$hq || !isset($hq['price']) || $hq['price'] === null) {
                return back()->withErrors(['close_value' => 'Sem cotação histórica disponível.']);
            }
            $price = (float)$hq['price'];
            $driver = DB::getDriverName();
            $isAcc = $this->computeAccuracy($asset_stat->p5, $asset_stat->p95, $price, null);
            if ($driver === 'sqlsrv') {
                DB::update(
                    'UPDATE [asset_daily_stats] SET [close_value]=?, [is_accurate]=? WHERE [id]=?'
                    , [$price, $isAcc, $asset_stat->id]
                );
            } else {
                $asset_stat->update(['close_value' => $price, 'is_accurate' => $isAcc]);
            }
            return back()->with('success', 'Fechado atualizado.');
        } catch (\Throwable $e) {
            return back()->withErrors(['close_value' => 'Falha ao atualizar fechado: '.$e->getMessage()]);
        }
    }

    public function recomputeAccuracy(Request $request)
    {
        $this->middleware('permission:ASSET STATS - EDITAR');
        $symbol = strtoupper(trim((string)$request->input('symbol')));
        $dateStartRaw = trim((string)$request->input('date_start'));
        $dateEndRaw = trim((string)$request->input('date_end'));
        $accFilter = trim((string)$request->input('acc', ''));
        $hasClose = $request->boolean('has_close');
        $q = AssetDailyStat::query();
        if ($symbol !== '') { $q->where('symbol', $symbol); }
        if ($hasClose) { $q->whereNotNull('close_value'); }
        $start = $this->parseFilterDate($dateStartRaw);
        $end = $this->parseFilterDate($dateEndRaw);
        $start = $start ? $start->startOfDay() : null;
        $end = $end ? $end->endOfDay() : null;
        if ($start && $end) {
            $q->whereBetween('date', [$start, $end]);
        } elseif ($start) {
            $q->where('date', '>=', $start);
        } elseif ($end) {
            $q->where('date', '<=', $end);
        }
        if ($accFilter === 'ok') { $q->where('is_accurate', true); }
        elseif ($accFilter === 'out') { $q->where('is_accurate', false); }
        elseif ($accFilter === 'na') { $q->whereNull('is_accurate'); }

        $svc = app(\App\Services\MarketDataService::class);
        $driver = DB::getDriverName();
        DB::beginTransaction();
        try {
            foreach ($q->cursor() as $row) {
                $close = $row->close_value;
                if ($close === null) {
                    $date = optional($row->date)->format('Y-m-d');
                    $hq = $svc->getHistoricalQuote($row->symbol, $date);
                    $close = ($hq && isset($hq['price']) && is_numeric($hq['price'])) ? (float)$hq['price'] : null;
                }
                $isAcc = $this->computeAccuracy($row->p5, $row->p95, $close, null);
                if ($driver === 'sqlsrv') {
                    DB::update('UPDATE [asset_daily_stats] SET [is_accurate]=?'.($close!==$row->close_value?' ,[close_value]=?':'').' WHERE [id]=?'
                        , $close!==$row->close_value ? [$isAcc, $close, $row->id] : [$isAcc, $row->id]);
                } else {
                    $payload = ['is_accurate' => $isAcc];
                    if ($close !== $row->close_value) { $payload['close_value'] = $close; }
                    $row->update($payload);
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['acc' => 'Falha ao recalcular: '.$e->getMessage()]);
        }
        return back()->with('success', 'Acurácia recalculada para o filtro atual.');
    }

    // Recalcula acurácia apenas para um registro
    public function recomputeAccuracyOne(AssetDailyStat $asset_stat)
    {
        // Permissão já coberta no __construct para editar
        $close = $asset_stat->close_value;
        $isAcc = $this->computeAccuracy($asset_stat->p5, $asset_stat->p95, $close, null);
        $driver = DB::getDriverName();
        if ($driver === 'sqlsrv') {
            DB::update('UPDATE [asset_daily_stats] SET [is_accurate]=? WHERE [id]=?', [$isAcc, $asset_stat->id]);
        } else {
            $asset_stat->update(['is_accurate' => $isAcc]);
        }
        return back()->with('success', 'Acurácia recalculada para o registro.');
    }

    // Preenche o close_value a partir dos Registros do OpenAI (mesma data, mesmo código) – ação por linha
    public function fillCloseFromRecords(AssetDailyStat $asset_stat)
    {
        // Apenas hoje/futuro? Aqui não restringimos; usamos qualquer dia para preencher a partir do registro
        $symbol = strtoupper(trim((string)$asset_stat->symbol));
        if ($symbol === '') { return back()->withErrors(['close_value' => 'Símbolo inválido.']); }
        // Chats do usuário com este código
        $userId = (int) Auth::id();
        $chatIds = OpenAIChat::where('user_id', $userId)
            ->whereNotNull('code')->whereRaw("LTRIM(RTRIM(code)) <> ''")
            ->whereRaw('UPPER(LTRIM(RTRIM(code))) = ?', [$symbol])
            ->pluck('id');
        if ($chatIds->isEmpty()) {
            return back()->withErrors(['close_value' => 'Nenhuma conversa com este código foi encontrada para o usuário.']);
        }
        $d = optional($asset_stat->date);
        if (!$d) { return back()->withErrors(['close_value' => 'Data inválida para o registro.']); }
        $start = $d->copy()->startOfDay();
        $end = $d->copy()->endOfDay();
        // Pega o último registro do dia (maior occurred_at)
        $record = OpenAIChatRecord::whereIn('chat_id', $chatIds)
            ->whereBetween('occurred_at', [$start, $end])
            ->orderBy('occurred_at','desc')
            ->first();
        if (!$record) {
            return back()->withErrors(['close_value' => 'Nenhum registro encontrado neste dia para este código.']);
        }
        // Apenas grava se estiver em branco
        if ($asset_stat->close_value !== null) {
            return back()->with('success', 'Fechado já preenchido. Nada a fazer.');
        }
        $close = (float)($record->amount ?? 0);
        $isAcc = $this->computeAccuracy($asset_stat->p5, $asset_stat->p95, $close, null);
        $driver = DB::getDriverName();
        if ($driver === 'sqlsrv') {
            DB::update('UPDATE [asset_daily_stats] SET [close_value]=?, [is_accurate]=? WHERE [id]=?', [$close, $isAcc, $asset_stat->id]);
        } else {
            $asset_stat->update(['close_value' => $close, 'is_accurate' => $isAcc]);
        }
        return back()->with('success', 'Fechado preenchido a partir do registro do dia.');
    }

    // Ação em massa: preenche close_value em branco a partir dos Registros (respeitando filtros atuais)
    public function fillCloseFromRecordsBulk(Request $request)
    {
        $symbol = strtoupper(trim((string)$request->input('symbol')));
        $dateStartRaw = trim((string)$request->input('date_start'));
        $dateEndRaw = trim((string)$request->input('date_end'));
        $accFilter = trim((string)$request->input('acc', ''));
        $hasClose = $request->boolean('has_close');

        $q = AssetDailyStat::query();
        if ($symbol !== '') { $q->where('symbol', $symbol); }
        if ($hasClose) { $q->whereNotNull('close_value'); }
        // Apenas em branco para esta ação
        $q->whereNull('close_value');
        $start = $this->parseFilterDate($dateStartRaw);
        $end = $this->parseFilterDate($dateEndRaw);
        $start = $start ? $start->startOfDay() : null;
        $end = $end ? $end->endOfDay() : null;
        if ($start && $end) { $q->whereBetween('date', [$start, $end]); }
        elseif ($start) { $q->where('date', '>=', $start); }
        elseif ($end) { $q->where('date', '<=', $end); }
        if ($accFilter === 'ok') { $q->where('is_accurate', true); }
        elseif ($accFilter === 'out') { $q->where('is_accurate', false); }
        elseif ($accFilter === 'na') { $q->whereNull('is_accurate'); }

        $userId = (int) Auth::id();
        $driver = DB::getDriverName();
        $updated = 0; $notFound = 0; $errors = 0;
        foreach ($q->cursor() as $row) {
            try {
                $symbolRow = strtoupper(trim((string)$row->symbol));
                $chatIds = OpenAIChat::where('user_id', $userId)
                    ->whereNotNull('code')->whereRaw("LTRIM(RTRIM(code)) <> ''")
                    ->whereRaw('UPPER(LTRIM(RTRIM(code))) = ?', [$symbolRow])
                    ->pluck('id');
                if ($chatIds->isEmpty()) { $notFound++; continue; }
                $d = optional($row->date); if (!$d) { $notFound++; continue; }
                $startD = $d->copy()->startOfDay(); $endD = $d->copy()->endOfDay();
                $rec = OpenAIChatRecord::whereIn('chat_id', $chatIds)
                    ->whereBetween('occurred_at', [$startD, $endD])
                    ->orderBy('occurred_at','desc')
                    ->first();
                if (!$rec) { $notFound++; continue; }
                $close = (float)($rec->amount ?? 0);
                $isAcc = $this->computeAccuracy($row->p5, $row->p95, $close, null);
                if ($driver === 'sqlsrv') {
                    DB::update('UPDATE [asset_daily_stats] SET [close_value]=?, [is_accurate]=? WHERE [id]=?', [$close, $isAcc, $row->id]);
                } else {
                    $row->update(['close_value' => $close, 'is_accurate' => $isAcc]);
                }
                $updated++;
            } catch (\Throwable $e) {
                $errors++;
            }
        }
        $msg = "Atualizados: {$updated} — Sem registro no dia/código: {$notFound} — Erros: {$errors}";
        return back()->with('success', $msg);
    }

    public function importForm()
    {
        return view('asset_stats.import');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'symbol' => ['nullable','string','max:32'],
            'payload' => ['nullable','string'],
            'file' => ['nullable','file','mimetypes:text/plain,text/csv,text/tsv,text/*,application/vnd.ms-excel','max:5120'],
            'overwrite' => ['nullable','boolean'],
        ]);
        $symbol = strtoupper(trim((string)$request->input('symbol')));
        $payload = '';
        // Se arquivo enviado, prioriza conteúdo do arquivo
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            try {
                $payload = (string) file_get_contents($request->file('file')->getRealPath());
            } catch (\Throwable $e) {
                return back()->withErrors(['file' => 'Não foi possível ler o arquivo: '.$e->getMessage()])->withInput();
            }
            // Detecta símbolo se não informado manualmente
            if ($symbol === '') {
                $detected = $this->detectSymbolFromFilename($request->file('file')->getClientOriginalName());
                if ($detected) { $symbol = $detected; }
            }
        } else {
            $payload = trim((string)$request->input('payload'));
        }
        if ($payload === '') {
            return back()->withErrors(['payload' => 'Informe um arquivo ou cole o conteúdo.'])->withInput();
        }
        if ($symbol === '') {
            return back()->withErrors(['symbol' => 'Símbolo não informado e não foi possível detectar pelo nome do arquivo.'])->withInput();
        }
        $rows = $this->parseTablePayload($payload);
        // Garante ordem ascendente por data na importação
        usort($rows, function($a, $b){
            return strcmp((string)($a['date'] ?? ''), (string)($b['date'] ?? ''));
        });
        if (count($rows) === 0) {
            return back()->withErrors(['payload' => 'Nenhuma linha reconhecida.'])->withInput();
        }
        $overwrite = (bool)$request->boolean('overwrite');
        $inserted = 0; $updated = 0; $skipped = 0;
        DB::beginTransaction();
        try {
            $driver = DB::getDriverName();
            $svc = app(\App\Services\MarketDataService::class);
            foreach ($rows as $r) {
                [$date, $stamp] = $this->normalizeToSqlsrvDatetime2($r['date']);
                // Buscar fechamento do dia (ou anterior útil) se disponível
                $hq = $svc->getHistoricalQuote($symbol, $r['date']);
                $close = ($hq && isset($hq['price']) && is_numeric($hq['price'])) ? (float)$hq['price'] : null;
                $isAcc = $this->computeAccuracy($r['p5'] ?? null, $r['p95'] ?? null, $close, null);
                if ($driver === 'sqlsrv') {
                    // Verifica existência
                    $existing = DB::select('SELECT [id] FROM [asset_daily_stats] WHERE [symbol]=? AND [date]=CAST(? AS DATETIME2(7))', [$symbol,$date]);
                    if ($existing && !$overwrite) { $skipped++; continue; }
                    $ts = $this->nowStampSqlsrv();
                    if ($existing) {
                        DB::update(
                            'UPDATE [asset_daily_stats]
                             SET [mean]=?,[median]=?,[p5]=?,[p95]=?,[close_value]=?,[is_accurate]=?,[updated_at]=CAST(? AS DATETIME2(7))
                             WHERE [id]=?',
                            [$r['mean'],$r['median'],$r['p5'],$r['p95'],$close,$isAcc,$ts,$existing[0]->id]
                        );
                        $updated++;
                    } else {
                        DB::insert(
                            'INSERT INTO [asset_daily_stats]([symbol],[date],[mean],[median],[p5],[p95],[close_value],[is_accurate],[created_at],[updated_at])
                             VALUES(?,CAST(? AS DATETIME2(7)),?,?,?,?,?,?,CAST(? AS DATETIME2(7)),CAST(? AS DATETIME2(7)))',
                            [$symbol,$date,$r['mean'],$r['median'],$r['p5'],$r['p95'],$close,$isAcc,$ts,$ts]
                        );
                        $inserted++;
                    }
                } else {
                    $existing = AssetDailyStat::where('symbol',$symbol)->where('date',$date)->first();
                    if ($existing && !$overwrite) { $skipped++; continue; }
                    if ($existing) {
                        $existing->update([
                            'mean'=>$r['mean'],
                            'median'=>$r['median'],
                            'p5'=>$r['p5'],
                            'p95'=>$r['p95'],
                            'close_value'=>$close,
                            'is_accurate'=>$isAcc,
                        ]);
                        $updated++;
                    } else {
                        AssetDailyStat::create([
                            'symbol'=>$symbol,
                            'date'=>$date,
                            'mean'=>$r['mean'],
                            'median'=>$r['median'],
                            'p5'=>$r['p5'],
                            'p95'=>$r['p95'],
                            'close_value'=>$close,
                            'is_accurate'=>$isAcc,
                        ]);
                        $inserted++;
                    }
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['payload' => 'Erro ao importar: '.$e->getMessage()]);
        }
        $msg = 'Importação concluída. Linhas lidas: '.count($rows)." — Inseridas: {$inserted} — Atualizadas: {$updated}";
        if ($skipped > 0) { $msg .= " — Ignoradas (já existiam): {$skipped}"; }
        if (!$overwrite && $updated === 0 && $skipped > 0) {
            $msg .= ' (Marque "Substituir existentes" para atualizar)';
        }
        return redirect()->route('asset-stats.index', ['symbol'=>$symbol])->with('success', $msg);
    }

    private function detectSymbolFromFilename(string $filename): ?string
    {
        // Remove path se houver
        $base = basename($filename);
        // Remove extensão
        $base = preg_replace('/\.[^.]+$/','',$base);
        $tokens = preg_split('/[_\-]/',$base) ?: [];
        $candidates = [];
        foreach ($tokens as $i => $tok) {
            $raw = trim($tok);
            if ($raw === '') continue;
            $lower = strtolower($raw);
            if (in_array($lower, ['projecoes','projecoes','dados','serie','seriehistorica','a'])) continue;
            // Pula coisas que parecem datas ou números puros
            if (preg_match('/^\d{1,4}$/',$raw)) continue;
            if (!preg_match('/[a-zA-Z]/',$raw)) continue; // precisa ter letra
            if (strlen($raw) < 2 || strlen($raw) > 16) continue;
            $candidates[] = $raw;
        }
        if (empty($candidates)) return null;
        // Se primeira palavra era 'projecoes', tenta a próxima candidata após ela
        if (isset($tokens[0]) && strtolower($tokens[0]) === 'projecoes') {
            // Já estamos pegando todas candidatas acima; prioriza a primeira
            return strtoupper($candidates[0]);
        }
        return strtoupper($candidates[0]);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'symbol' => ['required','string','max:32'],
            'date' => ['required','date'],
            'mean' => ['nullable','numeric'],
            'median' => ['nullable','numeric'],
            'p5' => ['nullable','numeric'],
            'p95' => ['nullable','numeric'],
            'close_value' => ['nullable','numeric'],
            'is_accurate' => ['nullable','boolean'],
        ]);
    }

    // Se P5 <= Fechado <= P95 então acertou; se faltar dados, preserva valor manual (se fornecido) ou null
    private function computeAccuracy($p5, $p95, $close, $manual = null): ?bool
    {
        if ($p5 !== null && $p95 !== null && $close !== null) {
            return ($close >= $p5 && $close <= $p95);
        }
        if ($manual === true || $manual === false) {
            return (bool)$manual;
        }
        return null;
    }

    /**
     * Aceita um payload tipo tabela/CSV com cabeçalhos: Data, Media, Mediana, P5, P95.
     * Datas aceitas: dd/mm/yyyy ou yyyy-mm-dd.
     */
    private function parseTablePayload(string $payload): array
    {
        $lines = preg_split('/\r?\n/', trim($payload));
        $rows = [];
        $header = null;
        $map = ['date'=>0,'mean'=>null,'median'=>null,'p5'=>null,'p95'=>null];
        foreach ($lines as $idx => $line) {
            $line = trim($line);
            if ($line === '') { continue; }
            // Tenta CSV: ; depois ,
            $parts = str_getcsv($line, ';');
            if (count($parts) <= 1) { $parts = str_getcsv($line, ','); }
            if (!$header) {
                // Detecta header textual
                $joined = strtolower(implode('|', array_map(fn($v)=>trim($v), $parts)));
                $hasText = preg_match('/data|date|m[ée]dia|median|mediana|p5|p95/', $joined) === 1;
                if ($hasText) {
                    $header = array_map(fn($v)=>strtolower(trim($v)), $parts);
                    // Se a primeira célula estiver vazia, assume que é 'data'
                    if (($header[0] ?? '') === '') { $header[0] = 'data'; }
                    // Monta o mapa de índices por nome
                    foreach ($header as $i => $name) {
                        $n = preg_replace('/\s+/', '', (string)$name);
                        $n = str_replace(['é','É'], 'e', $n);
                        if (in_array($n, ['data','date'], true)) { $map['date'] = $i; }
                        if (in_array($n, ['media','média','mean'], true)) { $map['mean'] = $i; }
                        if (in_array($n, ['mediana','median'], true)) { $map['median'] = $i; }
                        if ($n === 'p5') { $map['p5'] = $i; }
                        if ($n === 'p95') { $map['p95'] = $i; }
                    }
                    continue; // próxima linha são dados
                }
                // Sem header textual: assume posições padrão
                $header = ['data','media','mediana','p5','p95'];
                $map = ['date'=>0,'mean'=>2,'median'=>3,'p5'=>4,'p95'=>5];
            }
            // Normalizar número de colunas para evitar notice
            $parts = array_values($parts);
            $dateRaw = $parts[$map['date'] ?? 0] ?? '';
            $meanRaw = ($map['mean'] !== null) ? ($parts[$map['mean']] ?? null) : null;
            $medianRaw = ($map['median'] !== null) ? ($parts[$map['median']] ?? null) : null;
            $p5Raw = ($map['p5'] !== null) ? ($parts[$map['p5']] ?? null) : null;
            $p95Raw = ($map['p95'] !== null) ? ($parts[$map['p95']] ?? null) : null;
            // Fallbacks quando não houver mediana na planilha (ex.: só Média, P5, P95)
            if ($meanRaw === null && isset($parts[1])) { $meanRaw = $parts[1]; }
            $date = $this->normalizeDate($dateRaw);
            if (!$date) { continue; }
            $rows[] = [
                'date' => $date,
                'mean' => $this->toFloat($meanRaw),
                'median' => $this->toFloat($medianRaw),
                'p5' => $this->toFloat($p5Raw),
                'p95' => $this->toFloat($p95Raw),
            ];
        }
        return $rows;
    }

    private function looksLikeHeader(array $parts): bool
    {
        $joined = strtolower(implode(' ', $parts));
        return str_contains($joined, 'data') || str_contains($joined, 'date') || str_contains($joined, 'median') || str_contains($joined, 'mediana') || str_contains($joined, 'média') || str_contains($joined, 'media') || str_contains($joined, 'p5') || str_contains($joined, 'p95');
    }

    private function normalizeDate(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') return null;
        // dd/mm/yyyy
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $raw, $m)) {
            return $m[3].'-'.$m[2].'-'.$m[1];
        }
        // yyyy-mm-dd
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $raw, $m)) {
            return $raw;
        }
        return null;
    }

    // Para SQL Server: retorna [date, stamp], onde date é string 'Y-m-d H:i:s.u0' compatível com DATETIME2(7)
    private function normalizeToSqlsrvDatetime2(string $isoDate): array
    {
        // Usa meia-noite UTC com fração 7 dígitos
        $dt = \Carbon\Carbon::parse($isoDate.' 00:00:00', 'UTC');
        $stamp = $dt->format('Y-m-d H:i:s.u'); // 6 dígitos
        if (preg_match('/^(.*\.[0-9]{6})$/', $stamp)) {
            $stamp .= '0';
        }
        return [$stamp, $stamp];
    }

    private function nowStampSqlsrv(): string
    {
        $now = now()->timezone('UTC');
        $ts = $now->format('Y-m-d H:i:s.u');
        if (preg_match('/^(.*\.[0-9]{6})$/', $ts)) {
            $ts .= '0';
        }
        return $ts;
    }

    private function toFloat($v): ?float
    {
        if ($v === null) return null;
        $s = trim((string)$v);
        if ($s === '') return null;
        // trata números no formato brasileiro 1.234,56
        if (preg_match('/^[-+]?\d{1,3}(\.\d{3})*,\d+$/', $s)) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        }
        // remove % se houver
        $s = rtrim($s, '%');
        return is_numeric($s) ? (float)$s : null;
    }

    private function parseFilterDate(?string $raw): ?\Carbon\Carbon
    {
        $raw = trim((string)$raw);
        if ($raw === '') return null;
        try {
            // primeiro tenta padrão HTML date (YYYY-MM-DD)
            return \Carbon\Carbon::createFromFormat('Y-m-d', $raw, 'UTC');
        } catch (\Throwable $_) {}
        try {
            // tenta formato brasileiro (DD/MM/YYYY)
            return \Carbon\Carbon::createFromFormat('d/m/Y', $raw, 'UTC');
        } catch (\Throwable $_) {}
        return null;
    }
}
