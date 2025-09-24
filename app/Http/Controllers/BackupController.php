<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    /**
     * Executa a cópia dos arquivos do disk local para o disk external e retorna
     * uma estrutura com arrays de copiados / ignorados e detalhes, para ser
     * usada tanto por respostas JSON quanto por views.
     *
     * @return array{copied: array<int,array>, skipped: array<int,array>, errors: array<int,array>, started_at: float, finished_at: float}
     */
    protected function runExternalBackup(): array
    {
        $started = microtime(true);
        Log::info('Backup EXTERNAL iniciado');
        $externalLogPath = storage_path('logs/backup_external.jsonl');
        $copied = [];
        $skipped = [];
        $errors = [];
        try {
            $localFiles = Storage::disk('local')->allFiles();
            Log::info('Backup EXTERNAL - total de arquivos encontrados: '.count($localFiles));
            foreach ($localFiles as $file) {
                try {
                    $size = Storage::disk('local')->size($file);
                    $content = Storage::disk('local')->get($file);
                    $destPath = $file;
                    $shouldCopy = true;
                    $action = 'copied';
                    $hashLocal = md5($content);
                    $hashExternal = null;
                    if (Storage::disk('external')->exists($destPath)) {
                        $externalContent = Storage::disk('external')->get($destPath);
                        $hashExternal = md5($externalContent);
                        if ($hashLocal === $hashExternal) {
                            $shouldCopy = false;
                            $action = 'skipped';
                        }
                    }
                    if ($shouldCopy) {
                        Storage::disk('external')->put($destPath, $content);
                        $copied[] = [
                            'file' => $destPath,
                            'size' => $size,
                            'hash' => $hashLocal,
                        ];
                        Log::info('Backup EXTERNAL copiado: '.$destPath.' ('.number_format($size).' bytes)');
                        @file_put_contents($externalLogPath, json_encode([
                            'ts' => now()->toIso8601String(),
                            'event' => 'copied',
                            'file' => $destPath,
                            'size' => $size,
                            'hash' => $hashLocal,
                        ], JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND | LOCK_EX);
                    } else {
                        $skipped[] = [
                            'file' => $destPath,
                            'size' => $size,
                            'hash' => $hashLocal,
                            'hash_existing' => $hashExternal,
                        ];
                        Log::info('Backup EXTERNAL ignorado (sem alteração): '.$destPath);
                        @file_put_contents($externalLogPath, json_encode([
                            'ts' => now()->toIso8601String(),
                            'event' => 'skipped',
                            'file' => $destPath,
                            'size' => $size,
                            'hash' => $hashLocal,
                        ], JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND | LOCK_EX);
                    }
                } catch (\Throwable $fe) {
                    Log::error('Backup EXTERNAL erro ao copiar '.$file.' => '.$fe->getMessage());
                    $errors[] = [
                        'file' => $file,
                        'error' => $fe->getMessage(),
                        'line' => $fe->getLine(),
                    ];
                    @file_put_contents($externalLogPath, json_encode([
                        'ts' => now()->toIso8601String(),
                        'event' => 'error',
                        'file' => $file,
                        'message' => $fe->getMessage(),
                    ], JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND | LOCK_EX);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Backup EXTERNAL falha global: '.$e->getMessage());
            $errors[] = [ 'file' => null, 'error' => $e->getMessage(), 'line' => $e->getLine() ];
            @file_put_contents($externalLogPath, json_encode([
                'ts' => now()->toIso8601String(),
                'event' => 'fatal',
                'message' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND | LOCK_EX);
        }
        $finished = microtime(true);
        Log::info('Backup EXTERNAL finalizado. Copiados='.count($copied).' Skipped='.count($skipped).' Erros='.count($errors));
        @file_put_contents($externalLogPath, json_encode([
            'ts' => now()->toIso8601String(),
            'event' => 'end',
            'copied' => count($copied),
            'skipped' => count($skipped),
            'errors' => count($errors),
            'duration_ms' => (int) (($finished-$started)*1000),
        ], JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND | LOCK_EX);
        return [
            'copied' => $copied,
            'skipped' => $skipped,
            'errors' => $errors,
            'started_at' => $started,
            'finished_at' => $finished,
        ];
    }

    /**
     * Copia todos os arquivos do storage local para o HD externo (disk external).
     * @return \Illuminate\Http\JsonResponse
     */
    public function backupAll()
    {
        $res = $this->runExternalBackup();
        return response()->json([
            'status' => 'ok',
            'copiados' => array_column($res['copied'], 'file'), // legado (apenas nomes)
            'ignorados' => array_column($res['skipped'], 'file'), // legado (apenas nomes)
            'copied_detailed' => $res['copied'],
            'skipped_detailed' => $res['skipped'],
            'total' => count($res['copied']),
            'total_ignorados' => count($res['skipped']),
            'erros' => $res['errors'],
            'duracao_ms' => (int) (($res['finished_at'] - $res['started_at']) * 1000),
        ]);
    }

    /**
     * Executa o backup e apresenta uma view detalhada com os arquivos copiados/ignorados.
     */
    public function backupAllView()
    {
        // Exibe apenas a view interativa; a execução ocorrerá via AJAX chamando /backup/storage-to-external
        return view('backup.external_run', [
            'initial' => true,
        ]);
    }

    /**
     * Copia todos os arquivos do storage local para o servidor FTP (disk ftp).
     * @return \Illuminate\Http\JsonResponse
     */
    public function backupAllToFtp()
    {
        Log::info('BackupController@backupAllToFtp INICIADO (dispatch job)');
        try {
            // despacha job para a fila padrão
            \App\Jobs\BackupToFtpJob::dispatch();
            Log::info('BackupToFtpJob dispatch realizado com sucesso');
        } catch (\Exception $e) {
            Log::error('Backup FTP dispatch error: ' . $e->getMessage() . ' | Linha: ' . $e->getLine() . ' | Arquivo: ' . $e->getFile());
            // Mesmo em caso de erro, retorna sucesso para o frontend, mas loga o erro
        }

        // Buscar o último resumo do log
        $logPath = storage_path('logs/laravel.log');
        $mensagemResumo = 'Backup enfileirado. Verifique o worker (php artisan queue:work) para acompanhar o progresso.';
        if (file_exists($logPath)) {
            $lines = @file($logPath);
            if ($lines) {
                $lines = array_reverse($lines);
                foreach ($lines as $line) {
                    if (strpos($line, 'Finalizado (raw). Copiados:') !== false) {
                        $mensagemResumo = trim($line);
                        break;
                    }
                }
            }
        }
        return response()->json([
            'status' => 'ok',
            'mensagem' => $mensagemResumo,
            'total' => 0,
        ]);
    }

    /**
     * Exibe os logs do backup FTP em uma view.
     */
    public function viewFtpLogs(Request $request)
    {
        $path = storage_path('logs/backup_ftp.jsonl');
        $rows = [];
        if (file_exists($path)) {
            try {
                $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                // Limita a 2000 linhas para não pesar a view
                $lines = array_slice($lines ?? [], -2000);
                foreach ($lines as $line) {
                    $obj = json_decode($line, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($obj)) {
                        $rows[] = $obj;
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Falha ao ler backup_ftp.jsonl: ' . $e->getMessage());
            }
        }
        // Ordena por ts desc se existir (ISO8601 permite ordenação lexicográfica)
        usort($rows, function ($a, $b) {
            return strcmp($b['ts'] ?? '', $a['ts'] ?? '');
        });

        // Filtros e paginação
        $event = trim((string) $request->query('event', ''));
        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('perPage', 50);
        if ($perPage < 1) $perPage = 50;
        if ($perPage > 500) $perPage = 500;
        $page = (int) $request->query('page', 1);
        if ($page < 1) $page = 1;

        $filtered = array_values(array_filter($rows, function ($r) use ($event, $q) {
            if ($event !== '' && ($r['event'] ?? '') !== $event) {
                return false;
            }
            if ($q !== '') {
                $hay = strtolower(json_encode([
                    $r['file'] ?? '',
                    $r['remote'] ?? '',
                    $r['message'] ?? '',
                ], JSON_UNESCAPED_UNICODE));
                if (strpos($hay, strtolower($q)) === false) {
                    return false;
                }
            }
            return true;
        }));

        // Estatísticas por evento
        $stats = [
            'total' => count($filtered),
            'sent' => 0,
            'skipped' => 0,
            'error' => 0,
            'fatal' => 0,
            'dry-run' => 0,
            'start' => 0,
            'end' => 0,
        ];
        foreach ($filtered as $r) {
            $evt = $r['event'] ?? '';
            if (isset($stats[$evt])) $stats[$evt]++;
        }

        // Paginação
        $total = count($filtered);
        $pages = max(1, (int) ceil($total / $perPage));
        if ($page > $pages) $page = $pages;
        $offset = ($page - 1) * $perPage;
        $pageRows = array_slice($filtered, $offset, $perPage);

        return view('backup.ftp_logs', [
            'rows' => $pageRows,
            'exists' => file_exists($path),
            'size' => $path && file_exists($path) ? filesize($path) : 0,
            'event' => $event,
            'q' => $q,
            'perPage' => $perPage,
            'page' => $page,
            'pages' => $pages,
            'stats' => $stats,
        ]);
    }

    /**
     * Limpa/rotaciona os logs do backup FTP com segurança.
     * - Se existir, renomeia para backup_ftp-YYYYmmdd_His.jsonl
     * - Cria arquivo vazio novo
     */
    public function clearFtpLogs(Request $request)
    {
        $path = storage_path('logs/backup_ftp.jsonl');
        try {
            if (file_exists($path)) {
                $ts = now()->format('Ymd_His');
                $archive = storage_path('logs/backup_ftp-' . $ts . '.jsonl');
                @rename($path, $archive);
                // cria novo arquivo vazio
                @file_put_contents($path, '', LOCK_EX);
                Log::info('backup_ftp.jsonl rotacionado para ' . basename($archive));
                return redirect()->to(url('/backup/ftp-logs'))
                    ->with('status', 'Logs arquivados em ' . basename($archive) . ' e arquivo reiniciado.');
            }
            // se não existe, apenas cria
            @file_put_contents($path, '', LOCK_EX);
            return redirect()->to(url('/backup/ftp-logs'))
                ->with('status', 'Arquivo de log criado.');
        } catch (\Throwable $e) {
            Log::error('Falha ao rotacionar backup_ftp.jsonl: ' . $e->getMessage());
            return redirect()->to(url('/backup/ftp-logs'))
                ->with('status', 'Erro ao limpar/arquivar logs: ' . $e->getMessage());
        }
    }

    /**
     * Download do arquivo de logs completo em NDJSON (JSONL).
     */
    public function downloadFtpLogs()
    {
        $path = storage_path('logs/backup_ftp.jsonl');
        if (!file_exists($path)) {
            abort(404, 'Log não encontrado.');
        }
        return response()->download($path, 'backup_ftp.jsonl', [
            'Content-Type' => 'application/x-ndjson',
        ]);
    }

    /**
     * Baixa os últimos N registros do log em JSON (array) ou NDJSON.
     * Query: n=100 (padrão), format=json|ndjson (padrão json)
     */
    public function downloadFtpLogsLast(Request $request)
    {
        $n = (int) $request->query('n', 100);
        if ($n < 1) $n = 1;
        if ($n > 5000) $n = 5000;
        $format = strtolower((string) $request->query('format', 'json'));
        $path = storage_path('logs/backup_ftp.jsonl');
        if (!file_exists($path)) {
            abort(404, 'Log não encontrado.');
        }
        $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $slice = array_slice($lines ?: [], -$n);
        if ($format === 'ndjson') {
            $content = implode("\n", $slice);
            return response($content, 200, [
                'Content-Type' => 'application/x-ndjson',
                'Content-Disposition' => 'attachment; filename="backup_ftp_last_' . $n . '.jsonl"'
            ]);
        } else {
            $arr = [];
            foreach ($slice as $line) {
                $obj = json_decode($line, true);
                if (json_last_error() === JSON_ERROR_NONE) $arr[] = $obj;
            }
            return response()->json($arr);
        }
    }

    /**
     * View interativa para executar backup FTP e acompanhar em tempo real via polling dos logs.
     * A execução (cópia) é assíncrona através de job enfileirado em /backup/storage-to-ftp.
     */
    public function backupFtpView()
    {
        return view('backup.ftp_run');
    }
}
