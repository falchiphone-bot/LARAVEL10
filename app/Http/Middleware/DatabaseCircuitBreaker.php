<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\DatabaseDownAlert;

class DatabaseCircuitBreaker
{
    /**
     * Quando o banco ficou indisponível recentemente, devolve resposta imediata
     * evitando que cada request gere novo timeout custoso.
     */
    public function handle(Request $request, Closure $next)
    {
        $connection = config('database.default');
        $key = 'db:down:' . $connection;
        $connConfig = config("database.connections.$connection", []);
        $driver = $connConfig['driver'] ?? null;
        $host = $connConfig['host'] ?? ($connConfig['read']['host'] ?? null);
        $port = $connConfig['port'] ?? 1433;

        if (Cache::has($key)) {
            $info = Cache::get($key);
            $retryAfter = max(1, (int) (($info['until'] ?? now()->timestamp) - now()->timestamp));
            // Loga apenas esporadicamente (1x por ~5s) para não inundar
            $logKey = 'db:down:logthrottle:' . $connection;
            if (!Cache::has($logKey)) {
                Cache::put($logKey, 1, 5); // 5 segundos
                Log::warning('DATABASE CIRCUIT BREAKER ativo', [
                    'connection' => $connection,
                    'host' => $info['host'] ?? null,
                    'driver' => $info['driver'] ?? null,
                    'retry_after' => $retryAfter,
                ]);
            }

            // API? devolve JSON 503 com Retry-After
            $headers = [
                'Retry-After' => (string) $retryAfter,
                'X-DB-Circuit-Breaker' => '1',
                'X-DB-Retry-After' => (string) $retryAfter,
            ];

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'BANCO DE DADOS TEMPORARIAMENTE INDISPONÍVEL. AVISE O ADMINISTRADOR',
                    'message' => 'Estamos aguardando a restauração da conexão. Tente novamente em instantes.',
                    'retry_after_seconds' => $retryAfter,
                ], 503)->withHeaders($headers);
            }
            return response()->view('errors.database', [
                'exceptionMessage' => null,
                'connectionName' => $connection,
                'host' => $info['host'] ?? null,
                'database' => config("database.connections.$connection.database"),
                'driver' => $info['driver'] ?? null,
                'requestId' => $request->header('X-Request-ID'),
                'retryAfter' => $retryAfter,
                'breaker' => true,
            ], 503)->withHeaders($headers);
        }

        // Preflight leve: se driver sqlsrv e host configurado, tenta socket rápido (0.8s) para evitar ficar preso em long login timeout
        $preflightEnabled = filter_var(env('DB_PREFLIGHT_PROBE', true), FILTER_VALIDATE_BOOLEAN);
        if ($preflightEnabled && $driver === 'sqlsrv' && $host) {
            $probeKey = 'db:probe:'.$host.':'.$port;
            // Evita rodar probe em TODAS as requisições sob carga: limita a cada 3s
            if (!Cache::has($probeKey)) {
                Cache::put($probeKey, 1, 3);
                $start = microtime(true);
                $errno = 0; $errstr = '';
                $conn = @fsockopen($host, (int)$port, $errno, $errstr, 0.8); // timeout curto
                $elapsed = (microtime(true) - $start) * 1000; // ms
                if (!$conn) {
                    // Marca breaker com TTL curto (fallback rápido). Evita custar 15s em cada request.
                    $ttl = (int) env('DB_BREAKER_TTL_ON_PROBE_FAIL', 15);
                    if ($ttl > 0) {
                        Cache::put($key, [
                            'until' => now()->addSeconds($ttl)->timestamp,
                            'since' => now()->timestamp,
                            'driver' => $driver,
                            'host' => $host,
                            'probe_fail' => true,
                            'probe_elapsed_ms' => (int) $elapsed,
                            'error' => $errstr ?: $errno,
                        ], $ttl);
                        Log::warning('DATABASE PREFLIGHT PROBE FAIL', [
                            'host' => $host,
                            'port' => $port,
                            'elapsed_ms' => round($elapsed,1),
                            'error' => $errstr ?: $errno,
                            'ttl' => $ttl,
                        ]);
                        $headers = [
                            'Retry-After' => (string) $ttl,
                            'X-DB-Circuit-Breaker' => '1',
                            'X-DB-Preflight' => 'fail',
                        ];
                        if ($request->expectsJson()) {
                            return response()->json([
                                'error' => 'BANCO INDISPONÍVEL (pré-checagem falhou)',
                                'probe_elapsed_ms' => (int) $elapsed,
                                'retry_after_seconds' => $ttl,
                            ], 503)->withHeaders($headers);
                        }
                        // Envia e-mail de alerta (com supressão) na primeira detecção via pré-checagem
                        $alertTo = env('DB_ALERT_EMAIL');
                        if ($alertTo) {
                            $suppressMinutes = (int) env('DB_ALERT_SUPPRESS_MINUTES', 30);
                            $alertKey = 'db:alert:sent:'.$connection;
                            if (!Cache::has($alertKey)) {
                                Cache::put($alertKey, 1, now()->addMinutes($suppressMinutes));
                                try {
                                    $recipients = array_filter(array_map('trim', explode(',', $alertTo)));
                                    if (!empty($recipients)) {
                                        Mail::to($recipients)->send(new DatabaseDownAlert(
                                            $connection,
                                            $driver,
                                            $host,
                                            $connConfig['database'] ?? null,
                                            $errstr ?: ('Erro pré-checagem socket (código: '.$errno.')')
                                        ));
                                    }
                                } catch (\Throwable $mailEx) {
                                    Log::error('DATABASE ALERT MAIL FAIL (preflight)', [
                                        'error' => $mailEx->getMessage(),
                                        'connection' => $connection,
                                    ]);
                                }
                            }
                        }
                        return response()->view('errors.database', [
                            'exceptionMessage' => null,
                            'connectionName' => $connection,
                            'host' => $host,
                            'database' => $connConfig['database'] ?? null,
                            'driver' => $driver,
                            'requestId' => $request->header('X-Request-ID'),
                            'retryAfter' => $ttl,
                            'breaker' => true,
                        ], 503)->withHeaders($headers);
                    }
                } else {
                    fclose($conn);
                }
            }
        }

        return $next($request);
    }
}
