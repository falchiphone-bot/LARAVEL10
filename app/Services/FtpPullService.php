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

    public function __construct()
    {
        $this->localBase = trim(env('FTP_PULL_LOCAL_BASE', 'ftp_mirror'), '/');
        if ($this->localBase === '') { $this->localBase = 'ftp_mirror'; }
        $this->logFile = storage_path('logs/ftp_pull.jsonl');
        $this->maxFiles = (int) env('FTP_PULL_MAX_FILES', 10000);
        if ($this->maxFiles < 1) $this->maxFiles = 10000;
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

    $queue = [ trim($remoteStart, '/') ];
        $processedFiles = 0;
        $seenDirs = [];
        while ($queue) {
            $dir = array_shift($queue);
            $dirNorm = $dir === '' ? '' : trim($dir, '/');
            if (isset($seenDirs[$dirNorm])) { continue; }
            $seenDirs[$dirNorm] = true;

            // cria diretório local correspondente
            $localDirRel = $this->localBase . ($dirNorm ? '/' . $dirNorm : '');
            $this->ensureLocalDir($localDirRel);
            $counters['dirs']++;

            try {
                $disk = Storage::disk($this->ftpDisk);
                $subDirs = $disk->directories($dirNorm ?: '/');
                foreach ($subDirs as $sd) {
                    $normalized = ltrim($sd, '/');
                    if ($normalized !== '') { $queue[] = $normalized; }
                }
                $files = $disk->files($dirNorm ?: '/');
                foreach ($files as $filePath) {
                    $pathNorm = ltrim($filePath, '/');
                    $counters['files_total']++;
                    if ($processedFiles >= $this->maxFiles) {
                        $this->logLine(['event' => 'limit_reached', 'message' => 'Limite maxFiles atingido', 'max_files' => $this->maxFiles]);
                        break 2;
                    }
                    $processedFiles++;
                    $this->handleFile($pathNorm, $counters);
                }
            } catch (\Throwable $e) {
                $counters['errors']++;
                $this->logLine(['event' => 'error', 'remote' => $dirNorm, 'message' => 'scan: ' . $e->getMessage()]);
            }
        }

        $counters['duration_ms'] = (int) ((microtime(true) - $startedAt) * 1000);
        $this->logLine(['event' => 'end'] + $counters);
        return $counters;
    }

    protected function handleFile(string $remotePath, array &$counters): void
    {
        $remotePath = ltrim($remotePath, '/');
        $localRel = $this->localBase . '/' . $remotePath;
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
    }

    protected function ensureLocalDir(string $rel): void
    {
        $abs = storage_path('app/' . $rel);
        if (!is_dir($abs)) {
            @mkdir($abs, 0775, true);
            $this->logLine(['event' => 'mkdir', 'local' => $rel]);
        }
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
}
