<?php
use App\Services\MarketDataService;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$symbol = $argv[1] ?? 'AAPL';
$svc = app(MarketDataService::class);
$res = $svc->getQuote($symbol);

echo json_encode($res, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) . PHP_EOL;
