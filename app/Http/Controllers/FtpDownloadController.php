<?php

namespace App\Http\Controllers;

use App\Services\FtpBrowserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FtpDownloadController extends Controller
{
    protected FtpBrowserService $browser;
    protected string $blockedIp = '186.237.225.6'; // IP do servidor que NÃO deve consumir downloads

    public function __construct(FtpBrowserService $browser)
    {
        $this->browser = $browser;
    }

    /**
     * Lista diretórios e arquivos do FTP.
     */
    public function index(Request $request)
    {
        $this->denyIfBlockedIp($request);
        $dirEncoded = (string) $request->query('p', '');
        $dir = '';
        if ($dirEncoded !== '') {
            try { $dir = base64_decode(strtr($dirEncoded, '._-', '+/=')); } catch (\Throwable $e) { $dir = ''; }
        }
        $data = $this->browser->list($dir);
        return view('ftp.browser', [
            'dir' => $data['dir'],
            'parent' => $data['parent'],
            'directories' => $data['directories'],
            'files' => $data['files'],
            'error' => $data['error'] ?? null,
            'encoded' => fn(string $p) => rtrim(strtr(base64_encode($p), '+/=', '._-'), '='),
        ]);
    }

    /**
     * Download de um arquivo via streaming (sem carregar tudo em memória).
     */
    public function download(Request $request): StreamedResponse
    {
        $this->denyIfBlockedIp($request);
        $pathEncoded = (string) $request->query('f');
        if ($pathEncoded === '') abort(400, 'Parâmetro ausente.');
        try {
            $path = base64_decode(strtr($pathEncoded, '._-', '+/='));
        } catch (\Throwable $e) {
            abort(400, 'Parâmetro inválido.');
        }
        if ($path === false) abort(400, 'Parâmetro inválido.');
        $path = ltrim($path, '/');
        if (str_contains($path, '..')) abort(400, 'Path inválido.');

        $disk = Storage::disk('ftp');
        if (!$disk->exists($path)) abort(404, 'Arquivo não encontrado.');

        // Tenta obter stream
        try {
            $stream = $disk->readStream($path);
        } catch (\Throwable $e) {
            Log::error('FTP readStream falhou para '.$path.' => '.$e->getMessage());
            abort(500, 'Falha ao abrir stream FTP.');
        }
        if (!is_resource($stream)) abort(500, 'Não foi possível abrir o arquivo para leitura.');

        $filename = basename($path);
        Log::info('FTP download iniciado: '.$path.' por IP '.$request->ip());

        return response()->streamDownload(function () use ($stream, $path) {
            while (!feof($stream)) {
                echo fread($stream, 8192);
                @ob_flush(); flush();
            }
            fclose($stream);
            Log::info('FTP download concluído: '.$path);
        }, $filename);
    }

    /**
     * Dispara job para sincronizar (baixar) arquivos do FTP para storage local.
     */
    public function pullStart(Request $request)
    {
        $this->denyIfBlockedIp($request);
        try {
            \App\Jobs\FtpPullJob::dispatch('');
        } catch (\Throwable $e) {
            Log::error('FtpPullJob dispatch erro: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Falha ao enfileirar sync', 'error' => $e->getMessage()], 500);
        }
        return response()->json(['status' => 'ok', 'message' => 'Sincronização enfileirada']);
    }

    /**
     * Retorna as últimas N linhas do log ftp_pull.jsonl (default 80)
     */
    public function pullLogs(Request $request)
    {
        $this->denyIfBlockedIp($request);
        $n = (int) $request->query('n', 80);
        if ($n < 1) $n = 80; if ($n > 500) $n = 500;
        $path = storage_path('logs/ftp_pull.jsonl');
        $rows = [];
        if (file_exists($path)) {
            try {
                $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
                $lines = array_slice($lines, -$n);
                foreach ($lines as $line) {
                    $obj = json_decode($line, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($obj)) {
                        $rows[] = $obj;
                    }
                }
            } catch (\Throwable $e) {
                Log::error('pullLogs leitura falhou: ' . $e->getMessage());
            }
        }
        return response()->json($rows, 200, ['Cache-Control' => 'no-store']);
    }

    /**
     * Retorna JSON com status progressivo (arquivo ftp_pull_status.json) para UI exibir barra.
     */
    public function pullStatus(Request $request)
    {
        $this->denyIfBlockedIp($request);
        $file = storage_path('logs/ftp_pull_status.json');
        if (!is_file($file)) {
            return response()->json(['state' => 'idle'], 200, ['Cache-Control' => 'no-store']);
        }
        try {
            $json = @file_get_contents($file);
            $data = @json_decode($json, true);
            if (!is_array($data)) { $data = ['state' => 'idle']; }
        } catch (\Throwable $e) {
            $data = ['state' => 'error', 'message' => $e->getMessage()];
        }
        return response()->json($data, 200, ['Cache-Control' => 'no-store']);
    }

    public function pullCancel(Request $request)
    {
        $this->denyIfBlockedIp($request);
        try {
            app(\App\Services\FtpPullService::class)->triggerCancel();
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
        return response()->json(['status' => 'ok']);
    }

    public function pullReset(Request $request)
    {
        $this->denyIfBlockedIp($request);
        try {
            app(\App\Services\FtpPullService::class)->resetStatus();
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
        return response()->json(['status' => 'ok']);
    }

    /**
     * Exporta relatório da última execução (entre último 'start' e 'end').
     * /ftp-browser/pull-report?format=json|csv (default json)
     */
    public function pullReport(Request $request)
    {
        $this->denyIfBlockedIp($request);
        $format = strtolower($request->query('format', 'json'));
        $logPath = storage_path('logs/ftp_pull.jsonl');
        if (!is_file($logPath)) {
            abort(404, 'Log não encontrado.');
        }
        try {
            $lines = @file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        } catch (\Throwable $e) {
            abort(500, 'Falha ao ler log.');
        }
        if (empty($lines)) abort(404, 'Sem registros.');

        $segment = [];
        $foundEnd = false; $foundStart = false;
        for ($i = count($lines)-1; $i >=0; $i--) {
            $raw = $lines[$i];
            $obj = json_decode($raw, true);
            if (!is_array($obj)) continue;
            if (!$foundEnd) {
                if (($obj['event'] ?? null) === 'end') { $foundEnd = true; $segment[] = $obj; }
                continue; // até achar end ignoramos start logs anteriores
            }
            // Já achou end, agora acumula até o start correspondente
            $segment[] = $obj;
            if (($obj['event'] ?? null) === 'start') { $foundStart = true; break; }
        }
        if (!$foundEnd || !$foundStart) {
            abort(404, 'Execução completa não encontrada (start/end).');
        }
        $segment = array_reverse($segment);

        // Extrair dados
        $startLine = null; $endLine = null; $downloads = []; $errors=[]; $skips=[]; $mkdirs=[];
        foreach ($segment as $entry) {
            switch ($entry['event'] ?? '') {
                case 'start': $startLine = $entry; break;
                case 'end': $endLine = $entry; break;
                case 'download': $downloads[] = $entry; break;
                case 'error': $errors[] = $entry; break;
                case 'skip': $skips[] = $entry; break;
                case 'mkdir': $mkdirs[] = $entry; break;
            }
        }
        if (!$startLine || !$endLine) abort(404, 'Segmento inválido.');

        $summary = [
            'started_at' => $startLine['ts'] ?? null,
            'ended_at' => $endLine['ts'] ?? null,
            'duration_ms' => $endLine['duration_ms'] ?? null,
            'downloaded' => $endLine['downloaded'] ?? count($downloads),
            'skipped' => $endLine['skipped'] ?? count($skips),
            'errors' => $endLine['errors'] ?? count($errors),
            'dirs' => $endLine['dirs'] ?? null,
            'files_total' => $endLine['files_total'] ?? null,
            'bytes_downloaded' => $endLine['bytes_downloaded'] ?? array_sum(array_map(fn($d)=>$d['size'] ?? 0, $downloads)),
            'remote_root' => $startLine['remote_root'] ?? null,
            'local_base' => $startLine['local_base'] ?? null,
        ];

        if ($format === 'csv') {
            // CSV de downloads
            $fh = fopen('php://temp', 'w+');
            fputcsv($fh, ['remote','local','size','ts']);
            foreach ($downloads as $d) {
                fputcsv($fh, [
                    $d['remote'] ?? '',
                    $d['local'] ?? '',
                    $d['size'] ?? '',
                    $d['ts'] ?? '',
                ]);
            }
            rewind($fh);
            $csv = stream_get_contents($fh);
            fclose($fh);
            $filename = 'ftp_pull_downloads_' . date('Ymd_His') . '.csv';
            return response($csv, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"'
            ]);
        }

        // JSON completo
        $payload = [
            'summary' => $summary,
            'downloads' => $downloads,
            'errors' => $errors,
            'skipped' => $skips,
            'mkdir' => $mkdirs,
            'raw_events' => $segment, // pode ser pesado, mas útil. Se quiser podemos remover depois.
        ];
        $filename = 'ftp_pull_report_' . date('Ymd_His') . '.json';
        return response(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), 200, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"'
        ]);
    }

    protected function denyIfBlockedIp(Request $request): void
    {
        if ($request->ip() === $this->blockedIp || $this->isBlockedServerEnvironment($request)) {
            Log::warning('Acesso FTP Browser bloqueado. Client IP: '.$request->ip().' ServerAddr: '.($request->server('SERVER_ADDR') ?? 'n/a'));
            abort(403, 'Ambiente bloqueado para esta funcionalidade. Execute a partir de outra máquina.');
        }
    }

    /**
     * Verifica se o próprio servidor (ambiente onde o código executa) corresponde ao IP bloqueado.
     * Isso cobre cenários onde o request vem de outro IP mas a política exige bloquear quando a aplicação
     * está hospedada especificamente em um dado host.
     */
    protected function isBlockedServerEnvironment(Request $request): bool
    {
        try {
            $serverAddr = $request->server('SERVER_ADDR'); // pode ser interno (ex: 172.x docker)
            $hostIp = @gethostbyname(gethostname());
            // Também tenta HTTP_HOST resolvido
            $resolvedHost = null;
            if ($request->server('HTTP_HOST')) {
                $resolvedHost = @gethostbyname(preg_replace('/:\\d+$/', '', $request->server('HTTP_HOST')));
            }
            $candidates = array_filter([$serverAddr, $hostIp, $resolvedHost]);
            return in_array($this->blockedIp, $candidates, true);
        } catch (\Throwable $e) {
            return false; // Em caso de falha, não bloquear silenciosamente
        }
    }
}
