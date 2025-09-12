<?php
require __DIR__ . "/../vendor/autoload.php";
 = require_once __DIR__ . "/../bootstrap/app.php";
 = ->make(Illuminate\Contracts\Console\Kernel::class);
->bootstrap();
 = app(\App\Services\MarketDataService::class);
foreach (["AAPL","PETR4.SA"] as ) {
   = ->getQuote();
  echo , ": ", json_encode(, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE), "\n";
}
