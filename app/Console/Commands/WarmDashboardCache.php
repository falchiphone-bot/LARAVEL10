<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Services\DashboardCache;

class WarmDashboardCache extends Command
{
    protected $signature = 'dashboard:warm-cache';
    protected $description = 'Preenche o cache dos contadores do dashboard (cadastros e atletas)';

    public function handle(): int
    {
        $cad = DashboardCache::cadastrosCounts();
        Cache::put('cadastros_counts', $cad, 300);

        $ath = DashboardCache::athletesCounts();
        Cache::put('athletes_counts', $ath, 300);

        try {
            $fin = DashboardCache::financeCounts();
            Cache::put('finance_counts', $fin, 300);
        } catch (\Throwable $e) {
            // opcional: logar se necessário
        }

        $this->info('Dashboard caches aquecidos com sucesso.');
        return self::SUCCESS;
    }
}
