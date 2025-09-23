<?php

return [
    // Alpha Vantage
    'alpha_key' => env('ALPHAVANTAGE_KEY', env('ALPHAVANTAGE_API_KEY')),
    'alpha_daily_limit' => (int) env('ALPHAVANTAGE_DAILY_LIMIT', 500),
    'alpha_per_minute' => (int) env('ALPHAVANTAGE_PER_MINUTE', 5),

    // Yahoo via RapidAPI
    'rapidapi_key' => env('RAPIDAPI_KEY'),
    // Hosts podem ser fornecidos em RAPIDAPI_YH_HOSTS (separados por vírgula), ou RAPIDAPI_YH_HOST, ou RAPIDAPI_HOST
    'rapidapi_hosts' => (function () {
        $hostsEnv = env('RAPIDAPI_YH_HOSTS', env('RAPIDAPI_YH_HOST', env('RAPIDAPI_HOST', '')));
        $parts = array_map('trim', explode(',', (string) $hostsEnv));
        return array_values(array_filter($parts));
    })(),
    'rapidapi_region' => env('RAPIDAPI_YH_REGION', 'US'),
    'rapidapi_daily_limit' => (function(){ $v = env('RAPIDAPI_DAILY_LIMIT'); return is_numeric($v) && (int)$v > 0 ? (int)$v : null; })(),
    'rapidapi_per_minute' => (function(){ $v = env('RAPIDAPI_PER_MINUTE'); return is_numeric($v) && (int)$v > 0 ? (int)$v : null; })(),

    // HTTP (Guzzle) timeouts
    'http_timeout' => (float) env('RAPIDAPI_HTTP_TIMEOUT', 3.5),
    'http_connect_timeout' => (float) env('RAPIDAPI_HTTP_CONNECT_TIMEOUT', 2.0),

    // Cache de cotação
    'quote_cache_ttl' => (int) env('MARKET_QUOTE_CACHE_TTL', 60),
];
