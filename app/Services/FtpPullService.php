<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FtpPullService
{
    protected string $ftpDisk = 'ftp';
    protected string $localBase; // relativo a storage/app
    protected string $logFile;   // storage/logs/ftp_pull.jsonl
    protected int $maxFiles;
    protected string $statusFile; // storage/logs/ftp_pull_status.json

    public function __construct()
    {
        // Se a variável existir e estiver vazia, respeitamos (root). Se não existir, usamos ftp_mirror.
        $cfg = env('FTP_PULL_LOCAL_BASE');
        if ($cfg === null) {
            $cfg = 'ftp_mirror';
        }
        $this->localBase = trim($cfg, '/'); // pode ficar '' para root
        $this->logFile = storage_path('logs/ftp_pull.jsonl');
        $this->maxFiles = (int) env('FTP_PULL_MAX_FILES', 10000);
        if ($this->maxFiles < 1) $this->maxFiles = 10000;
        $this->statusFile = storage_path('logs/ftp_pull_status.json');
    }

    public function run(string $remoteStart = ''): array
    {
        $startedAt = microtime(true);
        $counters = [
            'downloaded' => 0,
            'skipped' => 0,
            'errors' => 0,
            'dirs' => 0,
            'files_total' => 0,
        ];
        $this->logLine(['event' => 'start', 'remote_root' => $remoteStart, 'local_base' => $this->localBase]);
        $startedIso = now()->toIso8601String();
        $processedFiles = 0;
        $this->updateStatus([
            'state' => 'running',
            'started_at' => $startedIso,
            'downloaded' => 0,
            'skipped' => 0,
            'errors' => 0,
            'dirs' => 0,
            'files_total' => 0,
            'processed' => 0,
            'current' => null,
            'remote_root' => $remoteStart,
            'local_base' => $this->localBase,
        ]);

        $queue = [ trim($remoteStart, '/') ];
        $seenDirs = [];
        while ($queue) {
            $dir = array_shift($queue);
            $dirNorm = $dir === '' ? '' : trim($dir, '/');
            if (isset($seenDirs[$dirNorm])) { continue; }
            $seenDirs[$dirNorm] = true;

            // cria diretório local correspondente
            $localDirRel = $this->buildLocalDirRel($dirNorm);
            if ($localDirRel !== '') { // root não precisa criar explicitamente
                $this->ensureLocalDir($localDirRel);
            }
            $counters['dirs']++;
            $this->updateStatusSnapshot($counters, $processedFiles, null);

            try {
                $disk = Storage::disk($this->ftpDisk);
                $subDirs = $disk->directories($dirNorm ?: '/');
                foreach ($subDirs as $sd) {
                    $normalized = trim($sd, '/');
                    if ($normalized !== '') { $queue[] = $normalized; }
                }
                $files = $disk->files($dirNorm ?: '/');
                foreach ($files as $filePath) {
                    $pathNorm = trim($filePath, '/');
                    $counters['files_total']++;
                    if ($processedFiles >= $this->maxFiles) {
                        $this->logLine(['event' => 'limit_reached', 'message' => 'Limite maxFiles atingido', 'max_files' => $this->maxFiles]);
                        $this->updateStatusSnapshot($counters, $processedFiles, null, 'limit');
                        break 2;
                    }
                    $processedFiles++;
                    $this->handleFile($pathNorm, $counters, $processedFiles);
                }
            } catch (\Throwable $e) {
                $counters['errors']++;
                $this->logLine(['event' => 'error', 'remote' => $dirNorm, 'message' => 'scan: ' . $e->getMessage()]);
                $this->updateStatusSnapshot($counters, $processedFiles, $dirNorm);
            }
        }

        $counters['duration_ms'] = (int) ((microtime(true) - $startedAt) * 1000);
        $this->logLine(['event' => 'end'] + $counters);
        $this->updateStatusSnapshot($counters, $processedFiles, null, 'finished');
        return $counters;
    }

    protected function handleFile(string $remotePath, array &$counters, int $processedFiles): void
    {
    $remotePath = trim($remotePath, '/');
    $localRel = $this->buildLocalFileRel($remotePath);
    $localAbs = storage_path('app/' . $localRel);

        // Skip se já existe mesmo size
        try {
            $remoteSize = Storage::disk($this->ftpDisk)->size($remotePath);
        } catch (\Throwable $e) {
            $counters['errors']++;
            $this->logLine(['event' => 'error', 'remote' => $remotePath, 'message' => 'size: ' . $e->getMessage()]);
            return;
        }
        $existing = is_file($localAbs) ? filesize($localAbs) : null;
        if ($existing !== null && (int)$existing === (int)$remoteSize) {
            $counters['skipped']++;
            $this->logLine(['event' => 'skip', 'remote' => $remotePath, 'local' => $localRel, 'size' => $remoteSize]);
            $this->updateStatusSnapshot($counters, $processedFiles, $remotePath);
            return;
        }

        // Garante diretório local
        $dirName = dirname($localAbs);
        if (!is_dir($dirName)) { @mkdir($dirName, 0775, true); }

        try {
            $stream = Storage::disk($this->ftpDisk)->readStream($remotePath);
            if ($stream === false) { throw new \RuntimeException('readStream retornou false'); }
            $dest = @fopen($localAbs, 'w');
            if ($dest === false) { throw new \RuntimeException('Falha ao abrir destino local'); }
            stream_copy_to_stream($stream, $dest);
            @fclose($stream); @fclose($dest);
            $counters['downloaded']++;
            $this->logLine(['event' => 'download', 'remote' => $remotePath, 'local' => $localRel, 'size' => $remoteSize]);
        } catch (\Throwable $e) {
            $counters['errors']++;
            $this->logLine(['event' => 'error', 'remote' => $remotePath, 'local' => $localRel, 'message' => $e->getMessage()]);
        }
        $this->updateStatusSnapshot($counters, $processedFiles, $remotePath);
    }

    protected function ensureLocalDir(string $rel): void
    {
        $abs = storage_path('app/' . $rel);
        if (!is_dir($abs)) {
            @mkdir($abs, 0775, true);
            $this->logLine(['event' => 'mkdir', 'local' => $rel]);
        }
    }

    protected function buildLocalDirRel(string $dirNorm): string
    {
        if ($this->localBase === '') {
            return trim($dirNorm, '/'); // root
        }
        return $this->localBase . ($dirNorm ? '/' . $dirNorm : '');
    }

    protected function buildLocalFileRel(string $remotePath): string
    {
        if ($this->localBase === '') {
            return $remotePath; // direto na raiz de storage/app
        }
        return $this->localBase . '/' . $remotePath;
    }

    protected function logLine(array $data): void
    {
        try {
            $record = array_merge([
                'ts' => now()->toIso8601String(),
                'type' => 'ftp_pull'
            ], $data);
            @file_put_contents($this->logFile, json_encode($record, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            Log::debug('FtpPullService log falhou: ' . $e->getMessage());
        }
    }

    protected function updateStatusSnapshot(array $counters, int $processed, ?string $current, string $state = 'running'): void
    {
        $payload = [
            'state' => $state,
            'started_at' => null,
            'downloaded' => $counters['downloaded'] ?? 0,
            'skipped' => $counters['skipped'] ?? 0,
            'errors' => $counters['errors'] ?? 0,
            'dirs' => $counters['dirs'] ?? 0,
            'files_total' => $counters['files_total'] ?? 0,
            'processed' => $processed,
            'current' => $current,
            'updated_at' => now()->toIso8601String(),
        ];
        // Tentar preservar started_at da primeira gravação
        if (is_file($this->statusFile)) {
            $old = @json_decode(@file_get_contents($this->statusFile), true);
            if (is_array($old) && !empty($old['started_at'])) {
                $payload['started_at'] = $old['started_at'];
            }
        }
        if ($payload['started_at'] === null) { $payload['started_at'] = now()->toIso8601String(); }
        $this->updateStatus($payload);
    }

    protected function updateStatus(array $data): void
    {
        try {
            @file_put_contents($this->statusFile, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        } catch (\Throwable $e) {
            // silencioso
        }
    }
}
