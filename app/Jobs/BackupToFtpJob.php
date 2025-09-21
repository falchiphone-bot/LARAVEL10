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
            Log::info('BackupToFtpJob: iniciando backup via Artisan::call');
            // chamamos o comando internamente (sem spawn de processo)
            $returnVar = Artisan::call('backup:ftp', ['--raw-ftp' => true, '--delay-ms' => 200]);
            $output = Artisan::output();
            Log::info('BackupToFtpJob finalizado. return: ' . $returnVar . ' | saída: ' . $output);
        } catch (\Exception $e) {
            Log::error('BackupToFtpJob erro: ' . $e->getMessage());
            throw $e;
        }
    }
}
