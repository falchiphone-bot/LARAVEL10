<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
            Log::info('BackupToFtpJob: iniciando backup via comando artisan');
            $php = defined('PHP_BINARY') ? PHP_BINARY : 'php';
            $artisan = base_path('artisan');
            $cmd = escapeshellcmd($php) . ' ' . escapeshellarg($artisan) . ' backup:ftp --raw-ftp --delay-ms=200';
            $output = [];
            $returnVar = 0;
            // executar comando e aguardar término (job roda no worker)
            exec($cmd, $output, $returnVar);
            Log::info('BackupToFtpJob finalizado. return: ' . $returnVar . ' | saída: ' . implode('\n', $output));
        } catch (\Exception $e) {
            Log::error('BackupToFtpJob erro: ' . $e->getMessage());
            throw $e;
        }
    }
}
