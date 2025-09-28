<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TestDatabaseConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:test {--clear-breaker : Clear database circuit breaker cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test database connection and circuit breaker status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('clear-breaker')) {
            $this->clearCircuitBreaker();
            return;
        }

        $this->info('🔍 Testando conectividade do banco de dados...');
        $this->newLine();

        // Teste 1: Configurações
        $connection = config('database.default');
        $config = config("database.connections.$connection");
        
        $this->info("📋 Configuração ativa: {$connection}");
        $this->line("   Host: {$config['host']}");
        $this->line("   Port: {$config['port']}");
        $this->line("   Database: {$config['database']}");
        $this->line("   Driver: {$config['driver']}");
        $this->newLine();

        // Teste 2: Socket TCP
        $this->info('🌐 Teste de socket TCP...');
        $start = microtime(true);
        $errno = 0; $errstr = '';
        $conn = @fsockopen($config['host'], $config['port'], $errno, $errstr, 2.0);
        $elapsed = (microtime(true) - $start) * 1000;

        if ($conn) {
            fclose($conn);
            $this->info("   ✅ Socket TCP: OK (" . round($elapsed, 1) . "ms)");
        } else {
            $this->error("   ❌ Socket TCP: FALHOU (" . round($elapsed, 1) . "ms) - {$errstr} [{$errno}]");
        }
        
        // Teste 3: Circuit Breaker Status
        $breakerKey = 'db:down:' . $connection;
        $this->info('⚡ Status do Circuit Breaker...');
        if (Cache::has($breakerKey)) {
            $info = Cache::get($breakerKey);
            $remaining = max(0, ($info['until'] ?? now()->timestamp) - now()->timestamp);
            $this->error("   🚫 CIRCUIT BREAKER ATIVO (resta {$remaining}s)");
            if (isset($info['probe_fail'])) {
                $this->line("      Motivo: Falha no preflight probe");
                $this->line("      Tempo de probe: {$info['probe_elapsed_ms']}ms");
            }
        } else {
            $this->info('   ✅ Circuit breaker: INATIVO');
        }

        // Teste 4: Conexão Laravel
        $this->info('🚀 Teste de conexão Laravel...');
        try {
            $start = microtime(true);
            $result = DB::select('SELECT 1 as test');
            $elapsed = (microtime(true) - $start) * 1000;
            $this->info("   ✅ Conexão Laravel: OK (" . round($elapsed, 1) . "ms)");
        } catch (\Exception $e) {
            $this->error("   ❌ Conexão Laravel: FALHOU - " . $e->getMessage());
        }

        $this->newLine();
        $this->info('💡 Use --clear-breaker para limpar o cache do circuit breaker se necessário');
    }

    private function clearCircuitBreaker()
    {
        $connection = config('database.default');
        $breakerKey = 'db:down:' . $connection;
        $alertKey = 'db:alert:sent:' . $connection;
        
        Cache::forget($breakerKey);
        Cache::forget($alertKey);
        
        $this->info('🧹 Circuit breaker cache limpo!');
        $this->info('   - Chave de bloqueio removida');
        $this->info('   - Chave de supressão de alertas removida');
    }
}
