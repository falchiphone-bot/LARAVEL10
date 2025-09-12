<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MarketDataService
{
    protected Client $http;

    public function __construct(?Client $client = null)
    {
        $this->http = $client ?: new Client([
            'timeout' => 6.0,
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

        return Cache::remember($cacheKey, 60, function () use ($symbol, $hasRapid) {
            if ($hasRapid) {
                $qh = $this->getQuoteYahoo($symbol);
                if ($qh['price'] !== null) { return $qh; }
                // se falhar, cai para Alpha
            }
            return $this->getQuoteAlpha($symbol);
        });
    }

    /**
     * Yahoo Finance via RapidAPI: tenta múltiplas rotas comuns (/v6/finance/quote, /market/v2/get-quotes, /stock/v2/get-summary)
     */
    protected function getQuoteYahoo(string $symbol): array
    {
        $host = env('RAPIDAPI_YH_HOST') ?: env('RAPIDAPI_HOST');
        $key  = env('RAPIDAPI_KEY');
        $region = env('RAPIDAPI_YH_REGION', 'US');
        if (!$host || !$key) {
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
                'query' => fn() => [ 'region' => $region, 'symbols' => $symbol ],
                'extract' => function(array $body) use ($symbol) {
                    $res = $body['quoteResponse']['result'][0] ?? null;
                    if (!$res) { return null; }
                    $price = isset($res['regularMarketPrice']) ? (float) $res['regularMarketPrice'] : null;
                    $currency = $res['currency'] ?? null;
                    $ts = $res['regularMarketTime'] ?? null;
                    return [
                        'symbol' => $symbol,
                        'price' => $price,
                        'currency' => $currency ?: 'USD',
                        'updated_at' => $ts ? gmdate('Y-m-d H:i:s', (int) $ts) : null,
                        'source' => 'yahoo_rapidapi',
                    ];
                }
            ],
            [
                'path' => '/market/v2/get-quotes',
                'query' => fn() => [ 'region' => $region, 'symbols' => $symbol ],
                'extract' => function(array $body) use ($symbol) {
                    $res = $body['quoteResponse']['result'][0] ?? null;
                    if (!$res) { return null; }
                    $price = isset($res['regularMarketPrice']) ? (float) $res['regularMarketPrice'] : null;
                    $currency = $res['currency'] ?? null;
                    $ts = $res['regularMarketTime'] ?? null;
                    return [
                        'symbol' => $symbol,
                        'price' => $price,
                        'currency' => $currency ?: 'USD',
                        'updated_at' => $ts ? gmdate('Y-m-d H:i:s', (int) $ts) : null,
                        'source' => 'yahoo_rapidapi',
                    ];
                }
            ],
            [
                'path' => '/stock/v2/get-summary',
                'query' => fn() => [ 'region' => $region, 'symbol' => $symbol ],
                'extract' => function(array $body) use ($symbol) {
                    $priceObj = $body['price']['regularMarketPrice'] ?? null;
                    $currency = $body['price']['currency'] ?? null;
                    $ts = $body['price']['regularMarketTime'] ?? null;
                    $price = is_array($priceObj) ? ($priceObj['raw'] ?? null) : (is_numeric($priceObj) ? (float)$priceObj : null);
                    if ($price === null) { return null; }
                    return [
                        'symbol' => $symbol,
                        'price' => (float) $price,
                        'currency' => $currency ?: 'USD',
                        'updated_at' => $ts ? gmdate('Y-m-d H:i:s', (int) $ts) : null,
                        'source' => 'yahoo_rapidapi',
                    ];
                }
            ],
        ];

        foreach ($attempts as $att) {
            try {
                $url = 'https://' . $host . $att['path'];
                $resp = $this->http->get($url, [
                    'query' => $att['query'](),
                    'headers' => [
                        'X-RapidAPI-Key' => $key,
                        'X-RapidAPI-Host' => $host,
                    ],
                    'http_errors' => false,
                ]);
                $code = $resp->getStatusCode();
                $raw = (string) $resp->getBody();
                $body = json_decode($raw, true);
                if ($code !== 200 || !is_array($body)) {
                    Log::warning('MarketData: Yahoo RapidAPI resposta inválida', [
                        'status' => $code,
                        'host' => $host,
                        'path' => $att['path'],
                        'body_head' => substr($raw, 0, 180)
                    ]);
                    continue;
                }
                $extracted = $att['extract']($body);
                if ($extracted && $extracted['price'] !== null) {
                    return $extracted;
                }
                Log::info('MarketData: Yahoo RapidAPI sem dados na rota', [
                    'host' => $host,
                    'path' => $att['path'],
                ]);
            } catch (\Throwable $t) {
                Log::warning('MarketData: exceção Yahoo RapidAPI', [
                    'host' => $host,
                    'path' => $att['path'],
                    'error' => $t->getMessage(),
                ]);
                continue;
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
}
