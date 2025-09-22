<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SimpleIpRateLimit
{
    /**
     * Limita acessos por IP a um determinado prefixo de chave.
     * Params via route middleware: attempts|decay (s)
     * Ex.: 'simple.limit:status,6,30' => até 6 acessos a cada 30s por IP para a chave 'status'.
     */
    public function handle(Request $request, Closure $next, string $key = 'default', int $attempts = 6, int $decay = 30)
    {
        $ip = $request->ip() ?: 'unknown';
        $cacheKey = sprintf('srl:%s:%s', $key, $ip);
        $count = (int) Cache::get($cacheKey, 0);
        if ($count >= $attempts) {
            return response()->json(['error' => 'Too Many Requests'], 429, [
                'Retry-After' => (string) $decay,
                'Cache-Control' => 'public, max-age=5',
            ]);
        }
        if ($count === 0) {
            Cache::put($cacheKey, 1, $decay);
        } else {
            Cache::increment($cacheKey);
        }
        return $next($request);
    }
}
