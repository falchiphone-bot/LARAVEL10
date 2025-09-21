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
            Log::info('BackupToFtpJob: INICIADO');
            Log::info('BackupToFtpJob: Chamando comando backup:ftp --raw-ftp --delay-ms=200');
            $returnVar = Artisan::call('backup:ftp', ['--raw-ftp' => true, '--delay-ms' => 200]);
            Log::info('BackupToFtpJob: Comando Artisan::call retornou código: ' . $returnVar);
            $output = Artisan::output();
            Log::info('BackupToFtpJob: Saída do comando backup:ftp:\n' . $output);
            Log::info('BackupToFtpJob: FINALIZADO');
        } catch (\Exception $e) {
            Log::error('BackupToFtpJob: ERRO - ' . $e->getMessage());
            throw $e;
        }
    }
}
