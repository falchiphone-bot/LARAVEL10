<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MarketDataService
{
    protected Client $http;

    public function __construct(?Client $client = null)
    {
        $this->http = $client ?: new Client([
            'timeout' => (float) env('RAPIDAPI_HTTP_TIMEOUT', 3.5),
            'connect_timeout' => (float) env('RAPIDAPI_HTTP_CONNECT_TIMEOUT', 2.0),
        ]);
    }

    /**
     * Retorna cotação atual do símbolo (ex.: AAPL).
     * Prioriza Yahoo Finance via RapidAPI quando configurado. Fallback: Alpha Vantage.
     * Cache leve por 60s.
     *
     * @return array{symbol:string, price:float|null, currency:?string, updated_at:?string, source:string}
     */
    public function getQuote(string $symbol): array
    {
        $symbol = strtoupper(trim($symbol));
        if ($symbol === '') {
            return [
                'symbol' => $symbol,
                'price' => null,
                'currency' => null,
                'updated_at' => null,
                'source' => 'none',
            ];
        }

        $hasRapid = (bool) (env('RAPIDAPI_KEY') && (env('RAPIDAPI_YH_HOST') ?: env('RAPIDAPI_HOST')));
    $cacheKey = 'md.quote.' . ($hasRapid ? 'yh.' : 'av.') . $symbol;
    $ttl = (int) env('MARKET_QUOTE_CACHE_TTL', 60);

        return Cache::remember($cacheKey, $ttl > 0 ? $ttl : 60, function () use ($symbol, $hasRapid) {
            if ($hasRapid) {
                $qh = $this->getQuoteYahoo($symbol);
                if ($qh['price'] !== null) { return $qh; }
                // se falhar, cai para Alpha
            }
            $av = $this->getQuoteAlpha($symbol);
            if ($av['price'] !== null) { return $av; }
            // Fallback 3: Stooq (gratuito)
            $st = $this->getQuoteStooq($symbol);
            return $st;
        });
    }

    /**
     * Retorna cotação histórica (fechamento diário) para a data imediatamente anterior à informada (YYYY-MM-DD),
     * procurando até alguns dias úteis anteriores.
     * @return array{symbol:string, price:float|null, currency:?string, date:?string, source:string}
     */
    public function getHistoricalQuote(string $symbol, string $date): array
    {
        $symbol = strtoupper(trim($symbol));
        if ($symbol === '') {
            return ['symbol'=>$symbol,'price'=>null,'currency'=>null,'date'=>null,'source'=>'none'];
        }
        // Normalizar data
        try { $base = new \DateTimeImmutable($date); } catch (\Throwable $e) { return ['symbol'=>$symbol,'price'=>null,'currency'=>null,'date'=>null,'source'=>'none']; }

        // Determinar dia útil anterior (NYSE) como alvo preferencial
        $preferred = null;
        try {
            $svc = app(\App\Services\HolidayService::class);
            $info = $svc->previousBusinessDayInfo(Carbon::instance(new \DateTime($date)));
            /** @var Carbon $pref */
            $pref = $info['date'] ?? null;
            if ($pref instanceof Carbon) { $preferred = $pref->format('Y-m-d'); }
        } catch (\Throwable $e) { $preferred = null; }

        // 1) Stooq (rápido e simples)
        $st = $this->getHistoricalStooq($symbol, $base, $preferred);
        if ($st['price'] !== null) { return $st; }

        // 2) Alpha Vantage (fallback)
        $av = $this->getHistoricalAlpha($symbol, $base, $preferred);
        return $av;
    }

    protected function getHistoricalStooq(string $symbol, \DateTimeImmutable $base, ?string $preferred = null): array
    {
        $upper = strtoupper($symbol);
        $candidates = [];
        $plain = strtolower(preg_replace('/\s+/', '', $symbol));
        $candidates[] = $plain; // original
        // B3: remover .SA
        if (str_ends_with($upper, '.SA')) { $candidates[] = strtolower(substr($upper, 0, -3)); }
        // US: stooq usa sufixo .us para muitos tickers
        if (!str_ends_with($upper, '.US')) {
            $candidates[] = $plain . '.us';
        } else {
            $candidates[] = $plain; // já vem com .us em maiúsculas; plain já cobre minúsculo
        }
        // Se veio com outro sufixo (.NY, .US, etc.), tentar sem sufixo e com .us
        if (strpos($plain, '.') !== false) {
            $baseSym = explode('.', $plain)[0];
            if ($baseSym) {
                $candidates[] = $baseSym;
                $candidates[] = $baseSym . '.us';
            }
        }
        $candidates = array_unique($candidates);
        $currency = (str_ends_with($upper, '.SA') ? 'BRL' : 'USD');
        foreach ($candidates as $cand) {
            try {
                $resp = $this->http->get('https://stooq.com/q/d/l/', [
                    'query' => [ 's' => $cand, 'i' => 'd' ],
                    'http_errors' => false,
                ]);
                if ($resp->getStatusCode() !== 200) { continue; }
                $csv = trim((string) $resp->getBody());
                if ($csv === '') { continue; }
                $lines = preg_split('/\r?\n/', $csv);
                if (count($lines) < 2) { continue; }
                $headers = str_getcsv($lines[0]);
                $dataRows = array_slice($lines, 1);
                // montar lista de alvos: 1) a data solicitada (exata), 2) previous business day (se houver), 3) 1..7 dias corridos anteriores
                $targets = [];
                $requested = $base->format('Y-m-d');
                $targets[] = $requested;
                if ($preferred && $preferred !== $requested) { $targets[] = $preferred; }
                for ($off = 1; $off <= 7; $off++) {
                    $targets[] = $base->modify("-{$off} day")->format('Y-m-d');
                }
                $targets = array_values(array_unique($targets));
                foreach ($targets as $target) {
                    foreach ($dataRows as $ln) {
                        $row = str_getcsv($ln);
                        $rowAssoc = [];
                        foreach ($headers as $i=>$h) { $rowAssoc[$h] = $row[$i] ?? null; }
                        if (($rowAssoc['Date'] ?? '') === $target) {
                            $priceStr = $rowAssoc['Close'] ?? null;
                            $price = is_numeric($priceStr) ? (float)$priceStr : null;
                            if ($price !== null) {
                                return [
                                    'symbol' => $symbol,
                                    'price' => $price,
                                    'currency' => $currency,
                                    'date' => $target,
                                    'source' => 'stooq',
                                ];
                            }
                        }
                    }
                }
            } catch (\Throwable $t) {
                Log::warning('MarketData: exceção Stooq histórico', ['error'=>$t->getMessage(),'sym'=>$symbol]);
                continue;
            }
        }
        return ['symbol'=>$symbol,'price'=>null,'currency'=>null,'date'=>null,'source'=>'stooq'];
    }

    protected function getHistoricalAlpha(string $symbol, \DateTimeImmutable $base, ?string $preferred = null): array
    {
        $apiKey = env('ALPHAVANTAGE_KEY') ?: env('ALPHAVANTAGE_API_KEY');
        if (!$apiKey) {
            return ['symbol'=>$symbol,'price'=>null,'currency'=>null,'date'=>null,'source'=>'alpha_vantage'];
        }
        try {
            $resp = $this->http->get('https://www.alphavantage.co/query', [
                'query' => [ 'function' => 'TIME_SERIES_DAILY', 'symbol' => $symbol, 'apikey' => $apiKey, 'outputsize' => 'compact' ],
                'http_errors' => false,
            ]);
            if ($resp->getStatusCode() !== 200) {
                return ['symbol'=>$symbol,'price'=>null,'currency'=>null,'date'=>null,'source'=>'alpha_vantage'];
            }
            $body = json_decode((string)$resp->getBody(), true);
            $series = $body['Time Series (Daily)'] ?? [];
            // montar alvos: 1) data solicitada (exata), 2) previous business day (se houver), 3) até 7 dias corridos para trás
            $targets = [];
            $requested = $base->format('Y-m-d');
            $targets[] = $requested;
            if ($preferred && $preferred !== $requested) { $targets[] = $preferred; }
            for ($off=1;$off<=7;$off++) {
                $targets[] = $base->modify("-{$off} day")->format('Y-m-d');
            }
            $targets = array_values(array_unique($targets));
            foreach ($targets as $target) {
                if (isset($series[$target])) {
                    $closeStr = $series[$target]['4. close'] ?? null;
                    $price = is_numeric($closeStr) ? (float)$closeStr : null;
                    if ($price !== null) {
                        return ['symbol'=>$symbol,'price'=>$price,'currency'=>'USD','date'=>$target,'source'=>'alpha_vantage'];
                    }
                }
            }
        } catch (\Throwable $t) {
            Log::warning('MarketData: exceção Alpha histórico', ['error'=>$t->getMessage(),'sym'=>$symbol]);
        }
        return ['symbol'=>$symbol,'price'=>null,'currency'=>null,'date'=>null,'source'=>'alpha_vantage'];
    }

    /**
     * Yahoo Finance via RapidAPI: tenta múltiplas rotas conhecidas com timeouts curtos.
     */
    protected function getQuoteYahoo(string $symbol): array
    {
    $hostEnv = env('RAPIDAPI_YH_HOSTS') ?: (env('RAPIDAPI_YH_HOST') ?: env('RAPIDAPI_HOST'));
    $hosts = array_values(array_filter(array_map('trim', explode(',', (string) $hostEnv))));
        $key  = env('RAPIDAPI_KEY');
        $region = env('RAPIDAPI_YH_REGION', 'US');
    if (empty($hosts) || !$key) {
            return [
                'symbol' => $symbol,
                'price' => null,
                'currency' => null,
                'updated_at' => null,
                'source' => 'yahoo_rapidapi',
            ];
        }
        $attempts = [
            [
                'path' => '/v6/finance/quote',
                'query' => function () use ($region, $symbol) { return ['region' => $region, 'symbols' => $symbol]; },
                'extract' => function(array $b) use ($symbol) { return $this->extractYahooQuote($symbol, $b); }
            ],
            [
                'path' => '/market/v2/get-quotes',
                'query' => function () use ($region, $symbol) { return ['region' => $region, 'symbols' => $symbol]; },
                'extract' => function(array $b) use ($symbol) { return $this->extractYahooQuote($symbol, $b); }
            ],
            [
                'path' => '/stock/v2/get-summary',
                'query' => function () use ($region, $symbol) { return ['region' => $region, 'symbol' => $symbol]; },
                'extract' => function(array $b) use ($symbol) {
                    $priceObj = $b['price']['regularMarketPrice'] ?? null;
                    $curr = $b['price']['currency'] ?? null;
                    $ts = $b['price']['regularMarketTime'] ?? null;
                    $price = is_array($priceObj) ? ($priceObj['raw'] ?? null) : (is_numeric($priceObj) ? (float)$priceObj : null);
                    if ($price === null) return null;
                    return [
                        'symbol' => $symbol,
                        'price' => (float) $price,
                        'currency' => $curr ?: 'USD',
                        'updated_at' => $ts ? gmdate('Y-m-d H:i:s', (int) $ts) : null,
                        'source' => 'yahoo_rapidapi',
                    ];
                }
            ],
            // Alguns hosts expõem rotas sob /stock/get-*
            [
                'path' => '/stock/get-quote',
                'query' => function () use ($region, $symbol) { return ['region' => $region, 'symbol' => $symbol, 'lang' => 'en-US']; },
                'extract' => function(array $b) use ($symbol) { return $this->extractYahooQuote($symbol, $b); }
            ],
            [
                'path' => '/stock/get-summary',
                'query' => function () use ($region, $symbol) { return ['region' => $region, 'symbol' => $symbol, 'lang' => 'en-US']; },
                'extract' => function(array $b) use ($symbol) {
                    // tentar o mesmo esquema de price.* do v2
                    return $this->extractYahooQuote($symbol, $b) ?? (function($bb) use ($symbol) {
                        $priceObj = $bb['price']['regularMarketPrice'] ?? null;
                        $curr = $bb['price']['currency'] ?? null;
                        $ts = $bb['price']['regularMarketTime'] ?? null;
                        $price = is_array($priceObj) ? ($priceObj['raw'] ?? null) : (is_numeric($priceObj) ? (float)$priceObj : null);
                        if ($price === null) return null;
                        return [
                            'symbol' => $symbol,
                            'price' => (float) $price,
                            'currency' => $curr ?: 'USD',
                            'updated_at' => $ts ? gmdate('Y-m-d H:i:s', (int) $ts) : null,
                            'source' => 'yahoo_rapidapi',
                        ];
                    })($b);
                }
            ],
            [
                'path' => '/stock/get-detail',
                'query' => function () use ($region, $symbol) { return ['region' => $region, 'symbol' => $symbol, 'lang' => 'en-US']; },
                'extract' => function(array $b) use ($symbol) { return $this->extractYahooQuote($symbol, $b); }
            ],
            [
                'path' => '/stock/get-prices',
                'query' => function () use ($region, $symbol) { return ['region' => $region, 'symbol' => $symbol]; },
                'extract' => function(array $b) use ($symbol) {
                    // alguns retornam prices[0].close/regularMarketPrice
                    $prices = $b['prices'] ?? null;
                    if (is_array($prices) && isset($prices[0])) {
                        $p = $prices[0];
                        $price = $p['close'] ?? ($p['regularMarketPrice'] ?? null);
                        if (is_array($price)) { $price = $price['raw'] ?? null; }
                        if (is_numeric($price)) {
                            return [
                                'symbol' => $symbol,
                                'price' => (float) $price,
                                'currency' => $b['price']['currency'] ?? 'USD',
                                'updated_at' => null,
                                'source' => 'yahoo_rapidapi',
                            ];
                        }
                    }
                    return $this->extractYahooQuote($symbol, $b);
                }
            ],
        ];

        foreach ($hosts as $host) {
            foreach ($attempts as $att) {
                try {
                    $url = 'https://' . $host . $att['path'];
                    $resp = $this->http->get($url, [
                        'query' => ($att['query'])(),
                        'headers' => [
                            'X-RapidAPI-Key' => $key,
                            'X-RapidAPI-Host' => $host,
                        ],
                        'http_errors' => false,
                        'timeout' => (float) env('RAPIDAPI_HTTP_TIMEOUT', 3.5),
                        'connect_timeout' => (float) env('RAPIDAPI_HTTP_CONNECT_TIMEOUT', 2.0),
                    ]);
                    $code = $resp->getStatusCode();
                    $raw = (string) $resp->getBody();
                    $body = json_decode($raw, true);
                    if ($code !== 200 || !is_array($body)) {
                        Log::warning('MarketData: Yahoo RapidAPI inválido', [
                            'status' => $code,
                            'host' => $host,
                            'path' => $att['path'],
                            'body_head' => substr($raw, 0, 180)
                        ]);
                        continue;
                    }
                    $ex = ($att['extract'])($body);
                    if ($ex && $ex['price'] !== null) {
                        return $ex;
                    }
                } catch (\Throwable $t) {
                    Log::warning('MarketData: exceção Yahoo RapidAPI', [
                        'host' => $host,
                        'path' => $att['path'],
                        'error' => $t->getMessage(),
                    ]);
                    continue;
                }
            }
        }

        return [
            'symbol' => $symbol,
            'price' => null,
            'currency' => null,
            'updated_at' => null,
            'source' => 'yahoo_rapidapi',
        ];
    }

    /**
     * Extrai preço/currency/time de várias estruturas comuns dos provedores do Yahoo via RapidAPI.
     */
    private function extractYahooQuote(string $symbol, array $body): ?array
    {
        // quoteResponse.result[0]
        $r = $body['quoteResponse']['result'][0] ?? null;
        if (is_array($r)) {
            $price = $r['regularMarketPrice'] ?? ($r['ask'] ?? ($r['bid'] ?? null));
            if (is_array($price)) { $price = $price['raw'] ?? null; }
            $curr = $r['currency'] ?? $r['financialCurrency'] ?? null;
            $ts = $r['regularMarketTime'] ?? null;
            if (is_numeric($price)) {
                return [
                    'symbol' => $symbol,
                    'price' => (float) $price,
                    'currency' => $curr ?: 'USD',
                    'updated_at' => $ts ? gmdate('Y-m-d H:i:s', (int) $ts) : null,
                    'source' => 'yahoo_rapidapi',
                ];
            }
        }
        // price.regularMarketPrice
        $priceObj = $body['price']['regularMarketPrice'] ?? null;
        if ($priceObj !== null) {
            $price = is_array($priceObj) ? ($priceObj['raw'] ?? null) : (is_numeric($priceObj) ? (float)$priceObj : null);
            if ($price !== null) {
                $curr = $body['price']['currency'] ?? $body['price']['financialCurrency'] ?? 'USD';
                $ts = $body['price']['regularMarketTime'] ?? null;
                return [
                    'symbol' => $symbol,
                    'price' => (float) $price,
                    'currency' => $curr,
                    'updated_at' => $ts ? gmdate('Y-m-d H:i:s', (int) $ts) : null,
                    'source' => 'yahoo_rapidapi',
                ];
            }
        }
        // financialData.currentPrice
        $fd = $body['financialData']['currentPrice'] ?? null;
        if ($fd !== null) {
            $price = is_array($fd) ? ($fd['raw'] ?? null) : (is_numeric($fd) ? (float)$fd : null);
            if ($price !== null) {
                $curr = $body['price']['currency'] ?? $body['financialData']['financialCurrency'] ?? 'USD';
                return [
                    'symbol' => $symbol,
                    'price' => (float) $price,
                    'currency' => $curr,
                    'updated_at' => null,
                    'source' => 'yahoo_rapidapi',
                ];
            }
        }
        // Root regularMarketPrice (menos comum)
        $rootPrice = $body['regularMarketPrice'] ?? null;
        if ($rootPrice !== null) {
            $price = is_array($rootPrice) ? ($rootPrice['raw'] ?? null) : (is_numeric($rootPrice) ? (float)$rootPrice : null);
            if ($price !== null) {
                return [
                    'symbol' => $symbol,
                    'price' => (float) $price,
                    'currency' => $body['currency'] ?? 'USD',
                    'updated_at' => null,
                    'source' => 'yahoo_rapidapi',
                ];
            }
        }
        return null;
    }

    /**
     * Alpha Vantage (fallback)
     */
    protected function getQuoteAlpha(string $symbol): array
    {
        $apiKey = env('ALPHAVANTAGE_KEY') ?: env('ALPHAVANTAGE_API_KEY');
        if (!$apiKey) {
            return [
                'symbol' => $symbol,
                'price' => null,
                'currency' => null,
                'updated_at' => null,
                'source' => 'alpha_vantage',
            ];
        }
        try {
            $resp = $this->http->get('https://www.alphavantage.co/query', [
                'query' => [
                    'function' => 'GLOBAL_QUOTE',
                    'symbol' => $symbol,
                    'apikey' => $apiKey,
                ],
                'http_errors' => false,
            ]);
            $code = $resp->getStatusCode();
            $body = json_decode((string) $resp->getBody(), true);
            if ($code !== 200 || !is_array($body)) {
                Log::warning('MarketData: resposta inválida Alpha Vantage', ['status' => $code]);
                return [
                    'symbol' => $symbol,
                    'price' => null,
                    'currency' => null,
                    'updated_at' => null,
                    'source' => 'alpha_vantage',
                ];
            }
            $quote = $body['Global Quote'] ?? [];
            $priceStr = $quote['05. price'] ?? null;
            $price = is_string($priceStr) ? (float) $priceStr : (is_numeric($priceStr) ? (float)$priceStr : null);
            $updated = $quote['07. latest trading day'] ?? ($quote['10. change percent'] ?? null);
            return [
                'symbol' => $symbol,
                'price' => $price,
                'currency' => 'USD',
                'updated_at' => $updated ? (string) $updated : null,
                'source' => 'alpha_vantage',
            ];
        } catch (\Throwable $t) {
            Log::warning('MarketData: exceção Alpha Vantage', ['error' => $t->getMessage()]);
            return [
                'symbol' => $symbol,
                'price' => null,
                'currency' => null,
                'updated_at' => null,
                'source' => 'alpha_vantage',
            ];
        }
    }

    /**
     * Stooq (CSV público): https://stooq.com/q/l/?s=aapl&f=sd2t2ohlcv&h&e=csv
     */
    protected function getQuoteStooq(string $symbol): array
    {
        // Mapeia símbolo para o formato do Stooq
        $candidates = [];
        $upper = strtoupper($symbol);
        $lower = strtolower($symbol);
        $candidates[] = $lower;
        // Para B3, tentar sem sufixo .SA
        if (str_ends_with($upper, '.SA')) {
            $candidates[] = strtolower(substr($upper, 0, -3));
        }
        // Remover caracteres especiais comuns
        $candidates = array_unique($candidates);

        foreach ($candidates as $cand) {
            try {
                $resp = $this->http->get('https://stooq.com/q/l/', [
                    'query' => [
                        's' => $cand,
                        'f' => 'sd2t2ohlcv', // inclui symbol, date, time, open, high, low, close, volume
                        'h' => '',
                        'e' => 'csv',
                    ],
                    'http_errors' => false,
                ]);
                $code = $resp->getStatusCode();
                $csv = (string) $resp->getBody();
                if ($code !== 200 || trim($csv) === '') {
                    Log::warning('MarketData: Stooq inválido', ['status' => $code, 'cand' => $cand]);
                    continue;
                }
                $lines = preg_split('/\r?\n/', trim($csv));
                if (count($lines) < 2) { continue; }
                $headers = str_getcsv($lines[0]);
                $values = str_getcsv($lines[1]);
                $row = [];
                foreach ($headers as $i => $h) {
                    $row[$h] = $values[$i] ?? null;
                }
                // Close pode ser N/D quando não existe
                $priceStr = $row['Close'] ?? null;
                $price = is_numeric($priceStr) ? (float) $priceStr : null;
                if ($price === null) { continue; }
                $date = $row['Date'] ?? null;
                $time = $row['Time'] ?? null;
                $updated = $date ? ($date . ($time ? (' ' . $time) : '')) : null;
                $currency = (str_ends_with($upper, '.SA') ? 'BRL' : 'USD');
                return [
                    'symbol' => $symbol,
                    'price' => $price,
                    'currency' => $currency,
                    'updated_at' => $updated,
                    'source' => 'stooq',
                ];
            } catch (\Throwable $t) {
                Log::warning('MarketData: exceção Stooq', ['error' => $t->getMessage(), 'cand' => $cand]);
                continue;
            }
        }

        return [
            'symbol' => $symbol,
            'price' => null,
            'currency' => null,
            'updated_at' => null,
            'source' => 'stooq',
        ];
    }
}
