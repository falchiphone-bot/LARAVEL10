<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class BackupToFtpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        // nenhum dado necessário por enquanto
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Registrar início do job no arquivo JSONL para o frontend saber que começou
            $this->jsonLine(['event' => 'job_start']);
            Log::info('BackupToFtpJob: INICIADO');
            Log::info('BackupToFtpJob: Chamando comando backup:ftp --raw-ftp --delay-ms=200');
            $returnVar = Artisan::call('backup:ftp', ['--raw-ftp' => true, '--delay-ms' => 200]);
            Log::info('BackupToFtpJob: Comando Artisan::call retornou código: ' . $returnVar);
            $output = Artisan::output();
            Log::info('BackupToFtpJob: Saída do comando backup:ftp:\n' . $output);
            Log::info('BackupToFtpJob: FINALIZADO');
            $this->jsonLine(['event' => 'job_end', 'return_code' => $returnVar]);
        } catch (\Exception $e) {
            Log::error('BackupToFtpJob: ERRO - ' . $e->getMessage());
            $this->jsonLine(['event' => 'job_fatal', 'message' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Registra linha JSON no backup_ftp.jsonl sem interromper fluxo em caso de erro.
     */
    protected function jsonLine(array $data): void
    {
        try {
            $record = array_merge([
                'ts' => now()->toIso8601String(),
                'type' => 'backup:ftp'
            ], $data);
            @file_put_contents(
                storage_path('logs/backup_ftp.jsonl'),
                json_encode($record, JSON_UNESCAPED_UNICODE) . PHP_EOL,
                FILE_APPEND | LOCK_EX
            );
        } catch (\Throwable $t) {
            // silencioso
        }
    }
}
