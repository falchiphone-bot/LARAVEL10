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
    protected string $cancelFile; // storage/logs/ftp_pull_cancel.flag

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
        $this->cancelFile = storage_path('logs/ftp_pull_cancel.flag');
    }

    public function run(string $remoteStart = ''): array
    {
        // Limpa sinal de cancelamento anterior
        if (is_file($this->cancelFile)) @unlink($this->cancelFile);

        $startedAt = microtime(true);
        $counters = [
            'downloaded' => 0,
            'skipped' => 0,
            'errors' => 0,
            'dirs' => 0,
            'files_total' => 0,
            'bytes_downloaded' => 0,
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
            'bytes_downloaded' => 0,
            'avg_bytes_per_sec' => 0,
            'eta_seconds' => null,
            'remote_root' => $remoteStart,
            'local_base' => $this->localBase,
        ]);

        $queue = [ trim($remoteStart, '/') ];
        $seenDirs = [];
        while ($queue) {
            $dir = array_shift($queue);
            if ($this->shouldCancel()) { $this->logLine(['event' => 'cancel']); $this->updateStatusSnapshot($counters, $processedFiles, null, 'cancelled', $startedAt); break; }
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
                    if ($this->shouldCancel()) { $this->logLine(['event' => 'cancel']); $this->updateStatusSnapshot($counters, $processedFiles, null, 'cancelled', $startedAt); break 2; }
                    $pathNorm = trim($filePath, '/');
                    $counters['files_total']++;
                    if ($processedFiles >= $this->maxFiles) {
                        $this->logLine(['event' => 'limit_reached', 'message' => 'Limite maxFiles atingido', 'max_files' => $this->maxFiles]);
                        $this->updateStatusSnapshot($counters, $processedFiles, null, 'limit', $startedAt);
                        break 2;
                    }
                    $processedFiles++;
                    $this->handleFile($pathNorm, $counters, $processedFiles, $startedAt);
                }
            } catch (\Throwable $e) {
                $counters['errors']++;
                $this->logLine(['event' => 'error', 'remote' => $dirNorm, 'message' => 'scan: ' . $e->getMessage()]);
                $this->updateStatusSnapshot($counters, $processedFiles, $dirNorm, 'running', $startedAt);
            }
        }

        $counters['duration_ms'] = (int) ((microtime(true) - $startedAt) * 1000);
        $this->logLine(['event' => 'end'] + $counters);
        // Se cancelado ou limite já ajustou state; caso contrário finaliza como finished
        if (!$this->shouldCancel()) {
            $this->updateStatusSnapshot($counters, $processedFiles, null, in_array($this->readState(), ['limit','cancelled']) ? $this->readState() : 'finished', $startedAt);
        }
        return $counters;
    }

    protected function handleFile(string $remotePath, array &$counters, int $processedFiles, float $startedAt): void
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
            $this->updateStatusSnapshot($counters, $processedFiles, $remotePath, 'running', $startedAt);
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
            $counters['bytes_downloaded'] += (int)$remoteSize;
            $this->logLine(['event' => 'download', 'remote' => $remotePath, 'local' => $localRel, 'size' => $remoteSize]);
        } catch (\Throwable $e) {
            $counters['errors']++;
            $this->logLine(['event' => 'error', 'remote' => $remotePath, 'local' => $localRel, 'message' => $e->getMessage()]);
        }
        $this->updateStatusSnapshot($counters, $processedFiles, $remotePath, 'running', $startedAt);
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

    protected function updateStatusSnapshot(array $counters, int $processed, ?string $current, string $state = 'running', ?float $startedAt = null): void
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
            'bytes_downloaded' => $counters['bytes_downloaded'] ?? 0,
            'avg_bytes_per_sec' => 0,
            'eta_seconds' => null,
            'duration_ms' => null,
            'duration_human' => null,
        ];
        // Tentar preservar started_at da primeira gravação
        if (is_file($this->statusFile)) {
            $old = @json_decode(@file_get_contents($this->statusFile), true);
            if (is_array($old) && !empty($old['started_at'])) {
                $payload['started_at'] = $old['started_at'];
            }
        }
        if ($payload['started_at'] === null) { $payload['started_at'] = now()->toIso8601String(); }
        // Calcular velocidade e ETA
        $elapsed = 0.0;
        if ($startedAt !== null) {
            $elapsed = microtime(true) - $startedAt;
        } elseif (!empty($payload['started_at'])) {
            try { $elapsed = max(0.001, now()->diffInSeconds($payload['started_at'])); } catch (\Throwable $e) { $elapsed = 0.0; }
        }
        if ($elapsed > 0) {
            $payload['avg_bytes_per_sec'] = (int) floor(($payload['bytes_downloaded'] ?? 0) / $elapsed);
        }
        if (($payload['files_total'] ?? 0) > 0 && $processed > 0 && $state === 'running') {
            $ratio = $processed / max(1, $payload['files_total']);
            if ($ratio > 0 && $elapsed > 1) {
                $payload['eta_seconds'] = (int) floor($elapsed * (1/$ratio - 1));
            }
        }
        // Duração final quando terminou ou cancelou/limit
        if (in_array($state, ['finished','cancelled','limit'])) {
            $durationMs = (int) round(($startedAt !== null ? (microtime(true) - $startedAt) : $elapsed) * 1000);
            $payload['duration_ms'] = $durationMs;
            $payload['duration_human'] = $this->formatDuration($durationMs);
        }
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

    protected function shouldCancel(): bool
    {
        return is_file($this->cancelFile);
    }

    protected function readState(): ?string
    {
        if (!is_file($this->statusFile)) return null;
        $js = @json_decode(@file_get_contents($this->statusFile), true);
        return is_array($js) ? ($js['state'] ?? null) : null;
    }

    public function triggerCancel(): void
    {
        @file_put_contents($this->cancelFile, '1');
    }

    public function resetStatus(): void
    {
        if (is_file($this->statusFile)) @unlink($this->statusFile);
        if (is_file($this->cancelFile)) @unlink($this->cancelFile);
    }

    protected function formatDuration(int $ms): string
    {
        $seconds = (int) floor($ms / 1000);
        $msRemainder = $ms % 1000;
        $h = intdiv($seconds, 3600);
        $seconds %= 3600;
        $m = intdiv($seconds, 60);
        $s = $seconds % 60;
        $parts = [];
        if ($h > 0) $parts[] = $h.'h';
        if ($m > 0) $parts[] = $m.'m';
        $parts[] = $s.'s';
        if ($h === 0 && $m === 0) {
            $parts[count($parts)-1] = $s.'s '.str_pad((string)$msRemainder,3,'0',STR_PAD_LEFT).'ms';
        }
        return implode(' ', $parts);
    }
}
