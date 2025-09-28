<?php

namespace App\Jobs;

use App\Services\FtpPullService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FtpPullJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $remoteRoot;

    public function __construct(string $remoteRoot = '')
    {
        $this->remoteRoot = $remoteRoot; // vazio = raiz
    }

    public function handle(FtpPullService $svc): void
    {
        try {
            Log::info('FtpPullJob: INICIADO root=' . $this->remoteRoot);
            $svc->run($this->remoteRoot);
            Log::info('FtpPullJob: FINALIZADO');
        } catch (\Throwable $e) {
            Log::error('FtpPullJob: ERRO ' . $e->getMessage());
            throw $e;
        }
    }
}
