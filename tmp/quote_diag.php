<?php
// Diagnóstico rápido da Yahoo RapidAPI com timeouts curtos

$symbol = $argv[1] ?? 'AAPL';

function readEnv($path) {
    $vars = [];
    $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = ltrim($line);
        if ($line === '' || $line[0] === '#') continue;
        $pos = strpos($line, '=');
        if ($pos === false) continue;
        $k = trim(substr($line, 0, $pos));
        $v = substr($line, $pos + 1);
        // remove aspas exteriores se existirem
        if ((str_starts_with($v, '"') && str_ends_with($v, '"')) || (str_starts_with($v, "'") && str_ends_with($v, "'"))) {
            $v = substr($v, 1, -1);
        }
        $vars[$k] = $v;
    }
    return $vars;
}

$env = readEnv(__DIR__ . '/../.env');
$hostEnv = $env['RAPIDAPI_YH_HOSTS'] ?? ($env['RAPIDAPI_YH_HOST'] ?? ($env['RAPIDAPI_HOST'] ?? ''));
$hosts = array_values(array_filter(array_map('trim', explode(',', $hostEnv))));
$key  = $env['RAPIDAPI_KEY'] ?? '';
$region = $env['RAPIDAPI_YH_REGION'] ?? 'US';

if (empty($hosts) || $key === '') {
    fwrite(STDERR, "Defina RAPIDAPI_KEY e RAPIDAPI_YH_HOST no .env\n");
    exit(2);
}

function hit($url, $host, $key) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_HTTPHEADER => [
            'X-RapidAPI-Key: ' . $key,
            'X-RapidAPI-Host: ' . $host,
        ],
    ]);
    $body = curl_exec($ch);
    $err  = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, $body, $err];
}

$routes = [
    ["/v6/finance/quote?region=$region&symbols=$symbol", 'v6/finance/quote'],
    ["/market/v2/get-quotes?region=$region&symbols=$symbol", 'market/v2/get-quotes'],
    ["/stock/v2/get-summary?region=$region&symbol=$symbol", 'stock/v2/get-summary'],
];

foreach ($hosts as $host) {
    echo "Host: $host | Symbol: $symbol | Region: $region\n";
    foreach ($routes as [$path, $name]) {
        $url = 'https://' . $host . $path;
        [$code, $body, $err] = hit($url, $host, $key);
        echo "-- $name => HTTP $code" . ($err ? " | cURL: $err" : '') . "\n";
        if ($body) {
            echo substr($body, 0, 240) . "\n";
        }
    }
}
