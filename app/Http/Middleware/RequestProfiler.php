<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RequestProfiler
{
    /**
     * Handle an incoming request.
     * Ativa profiling quando ?profile=1 ou env REQUEST_PROFILING=true.
     */
    public function handle(Request $request, Closure $next)
    {
        $enabled = $request->boolean('profile') || filter_var(env('REQUEST_PROFILING', false), FILTER_VALIDATE_BOOLEAN);

        if (!$enabled) {
            return $next($request);
        }

        $start = microtime(true);
        $queries = [];
        $totalSqlTime = 0.0;

        DB::listen(function ($query) use (&$queries, &$totalSqlTime) {
            $sql = $query->sql;
            $bindings = $query->bindings;
            $time = $query->time; // ms
            $queries[] = [
                'sql' => $sql,
                'time_ms' => $time,
                'bindings' => $bindings,
            ];
            $totalSqlTime += (float) $time;
        });

        $response = $next($request);

        $duration = (microtime(true) - $start) * 1000.0; // ms
        $path = $request->method().' '.$request->getPathInfo();

        // Resumo enxuto
        Log::info(sprintf(
            'PROFILER %s | total=%.2fms sql=%.2fms queries=%d mem=%.2fMB',
            $path,
            $duration,
            $totalSqlTime,
            count($queries),
            memory_get_peak_usage(true) / (1024*1024)
        ));

        // Logar top N queries quando houver queries
        if (!empty($queries)) {
            // Ordena por tempo decrescente
            usort($queries, function ($a, $b) {
                return ($b['time_ms'] ?? 0) <=> ($a['time_ms'] ?? 0);
            });
            $topN = array_slice($queries, 0, 5);
            $i = 1;
            foreach ($topN as $q) {
                $sql = (string) ($q['sql'] ?? '');
                // Evita logs gigantes
                $sql = preg_replace('/\s+/', ' ', $sql);
                if (strlen($sql) > 400) {
                    $sql = substr($sql, 0, 400) . '…';
                }
                Log::info(sprintf(
                    'PROFILER-QUERY #%d %s | %.2fms | %s',
                    $i++, $path, (float)($q['time_ms'] ?? 0), $sql
                ));
            }
        }

        // Opcional: quando profiling ativo, anexa cabeçalhos com tempos
        if (method_exists($response, 'headers')) {
            $response->headers->set('X-Profile-Total-ms', sprintf('%.2f', $duration));
            $response->headers->set('X-Profile-SQL-ms', sprintf('%.2f', $totalSqlTime));
            $response->headers->set('X-Profile-Queries', (string) count($queries));
        }

        return $response;
    }
}
