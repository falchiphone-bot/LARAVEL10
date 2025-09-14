<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\MarketDataController;

$controller = app(MarketDataController::class);

$tests = [
  ['sym' => 'AAPL', 'date' => '2025-09-04'],
  ['sym' => 'AAPL', 'date' => '04/09/2025'],
  ['sym' => 'PETR4.SA', 'date' => '2025-09-04'],
  ['sym' => 'PETR4.SA', 'date' => '04/09/2025'],
];

foreach ($tests as $t) {
    $req = Request::create('/api/market/historical-quote', 'GET', [
        'symbol' => $t['sym'],
        'date' => $t['date'],
    ]);
    try {
        $res = $controller->historicalQuote($req);
        $status = $res->getStatusCode();
        $body = $res->getContent();
    } catch (Throwable $e) {
        $status = 500;
        $body = json_encode(['error' => $e->getMessage()]);
    }
    echo $t['sym'], ' ', $t['date'], ' => [', $status, '] ', $body, "\n";
}
