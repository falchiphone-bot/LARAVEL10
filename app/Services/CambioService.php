<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CambioService
{
    protected function http()
    {
        $client = Http::timeout(8)->retry(1, 200);
        if (config('cambio.insecure')) {
            $client = $client->withoutVerifying();
        }
        return $client;
    }

    /**
     * Retorna a cotação da moeda para BRL na data informada (YYYY-MM-DD)
     * @return array{valor: float, codigo: string, data_utilizada: string, provider?: string}|null
     */
    public function cotacaoParaBRL(string $moedaNomeOuCodigo, string $data): ?array
    {
        $codigo = $this->resolverCodigoMoeda($moedaNomeOuCodigo);

        if ($codigo === 'BRL') {
            return ['valor' => 1.0, 'codigo' => 'BRL', 'data_utilizada' => $data];
        }

        $url = sprintf('https://api.exchangerate.host/%s?base=%s&symbols=BRL', urlencode($data), urlencode($codigo));

        try {
            $res = $this->http()->get($url);
            if (config('cambio.log_payload')) {
                Log::debug('CambioService: exchangerate.host single payload', ['body' => $res->json(), 'url' => $url]);
            }
            if ($res->ok()) {
                $body = $res->json();
                $rate = $body['rates']['BRL'] ?? null;
                if ($rate) {
                    return ['valor' => (float) $rate, 'codigo' => $codigo, 'data_utilizada' => $data, 'provider' => 'exchangerate.host'];
                }
            } else {
                Log::warning('CambioService: resposta não OK (single date)', ['status' => $res->status(), 'url' => $url]);
            }

            // Fallback 1: tentar via EUR base calculando razão (BRL/USD)
            $ratio = $this->ratioFromEURBase($codigo, $data);
            if ($ratio) {
                return $ratio;
            }

            // Fallback 2: buscar última cotação disponível até N dias antes (timeseries)
            $lookback = (int) config('cambio.timeseries_lookback_days', 14);
            $start = \Carbon\Carbon::parse($data)->subDays($lookback)->toDateString();
            $urlTs = sprintf('https://api.exchangerate.host/timeseries?start_date=%s&end_date=%s&symbols=USD,BRL',
                urlencode($start), urlencode($data));
            $resTs = $this->http()->get($urlTs);
            if (config('cambio.log_payload')) {
                Log::debug('CambioService: exchangerate.host timeseries payload', ['body' => $resTs->json(), 'url' => $urlTs]);
            }
            if (!$resTs->ok()) {
                Log::warning('CambioService: resposta não OK (timeseries)', ['status' => $resTs->status(), 'url' => $urlTs]);
                $fr = $this->fromFrankfurter($codigo, $data);
                if ($fr) return $fr;
                return null;
            }
            $bodyTs = $resTs->json();
            $rates = $bodyTs['rates'] ?? [];
            if (!is_array($rates) || empty($rates)) {
                Log::info('CambioService: timeseries vazio', ['url' => $urlTs]);
                $fr = $this->fromFrankfurter($codigo, $data);
                if ($fr) return $fr;
                return null;
            }
            // Procura a data mais recente disponível até a escolhida
            krsort($rates);
            foreach ($rates as $dataKey => $valores) {
                // Se temos BRL e USD com base EUR, taxa USD->BRL = BRL / USD
                if (isset($valores['BRL']) && isset($valores['USD'])) {
                    $rate = (float) $valores['BRL'] / (float) $valores['USD'];
                    return [
                        'valor' => $codigo === 'USD' ? $rate : ($this->convertViaUSD($codigo, $dataKey, $rates[$dataKey]) ?? $rate),
                        'codigo' => $codigo,
                        'data_utilizada' => $dataKey,
                        'provider' => 'exchangerate.host',
                    ];
                }
            }
            Log::info('CambioService: nenhuma cotação BRL encontrada no intervalo', ['url' => $urlTs]);
            // Tenta Frankfurter como último fallback
            $fr = $this->fromFrankfurter($codigo, $data);
            if ($fr) return $fr;
            return null;
        } catch (\Throwable $e) {
            Log::error('CambioService: exceção ao consultar API', ['message' => $e->getMessage()]);
            // Tenta Frankfurter mesmo em exceção
            try {
                $fr = $this->fromFrankfurter($codigo, $data);
                if ($fr) return $fr;
            } catch (\Throwable $e2) {
                Log::error('CambioService: exceção ao consultar Frankfurter', ['message' => $e2->getMessage()]);
            }
            return null;
        }
    }

    /**
     * Tenta calcular a taxa USD->BRL via EUR base (símbolos USD,BRL) no dia.
     */
    protected function ratioFromEURBase(string $codigo, string $data): ?array
    {
        $url = sprintf('https://api.exchangerate.host/%s?symbols=USD,BRL', urlencode($data));
        $res = $this->http()->get($url);
        if (config('cambio.log_payload')) {
            Log::debug('CambioService: ratioFromEURBase single payload', ['body' => $res->json(), 'url' => $url]);
        }
        if (!$res->ok()) return null;
        $body = $res->json();
        $r = $body['rates'] ?? null;
        if (!is_array($r) || !isset($r['USD']) || !isset($r['BRL'])) {
            Log::info('CambioService: ratioFromEURBase sem USD/BRL', ['url' => $url, 'rates_keys' => is_array($r) ? array_keys($r) : null]);
            return null;
        }
        // USD->BRL = BRL / USD
        $rateUsdToBrl = (float) $r['BRL'] / (float) $r['USD'];

        if ($codigo === 'USD') {
            return ['valor' => $rateUsdToBrl, 'codigo' => 'USD', 'data_utilizada' => $data, 'provider' => 'exchangerate.host'];
        }

        // Para outras moedas, tenta converter via USD se possível (precisa da taxa MOEDA->USD)
        // Faz uma segunda chamada com símbolos: USD e a moeda desejada para obter EUR->USD e EUR->MOEDA
        $url2 = sprintf('https://api.exchangerate.host/%s?symbols=USD,%s', urlencode($data), urlencode($codigo));
        $res2 = $this->http()->get($url2);
        if (config('cambio.log_payload')) {
            Log::debug('CambioService: ratioFromEURBase aux payload', ['body' => $res2->json(), 'url' => $url2]);
        }
        if (!$res2->ok()) return null;
        $r2 = $res2->json()['rates'] ?? null;
        if (!is_array($r2) || !isset($r2['USD']) || !isset($r2[$codigo])) return null;
        // EUR->USD = r2['USD']; EUR->MOEDA = r2[$codigo]
        // MOEDA->USD = (EUR->USD) / (EUR->MOEDA)
        $moedaToUsd = (float) $r2['USD'] / (float) $r2[$codigo];
        // MOEDA->BRL = (MOEDA->USD) * (USD->BRL)
        $moedaToBrl = $moedaToUsd * $rateUsdToBrl;
        return ['valor' => $moedaToBrl, 'codigo' => $codigo, 'data_utilizada' => $data, 'provider' => 'exchangerate.host'];
    }

    /**
     * Converte via USD com base EUR usando um snapshot de rates (USD e moeda).
     */
    protected function convertViaUSD(string $codigo, string $data, array $ratesForDay): ?float
    {
        if ($codigo === 'USD') {
            if (isset($ratesForDay['BRL']) && isset($ratesForDay['USD'])) {
                return (float) $ratesForDay['BRL'] / (float) $ratesForDay['USD'];
            }
            return null;
        }
        if (!isset($ratesForDay['USD']) || !isset($ratesForDay[$codigo]) || !isset($ratesForDay['BRL'])) return null;
        $eurToUsd = (float) $ratesForDay['USD'];
        $eurToMoeda = (float) $ratesForDay[$codigo];
        $eurToBrl = (float) $ratesForDay['BRL'];
        // MOEDA->USD = (EUR->USD)/(EUR->MOEDA); USD->BRL = (EUR->BRL)/(EUR->USD)
        $moedaToUsd = $eurToUsd / $eurToMoeda;
        $usdToBrl = $eurToBrl / $eurToUsd;
        return $moedaToUsd * $usdToBrl;
    }

    /**
     * Fallback: Frankfurter retorna a última data útil automaticamente se a data pedida não tiver cotação
     * https://www.frankfurter.app/docs/
     */
    protected function fromFrankfurter(string $codigo, string $data): ?array
    {
        if ($codigo === 'BRL') {
            return ['valor' => 1.0, 'codigo' => 'BRL', 'data_utilizada' => $data, 'provider' => 'frankfurter'];
        }
        $url = sprintf('https://api.frankfurter.app/%s?from=%s&to=BRL', urlencode($data), urlencode($codigo));
        $res = $this->http()->get($url);
        if (config('cambio.log_payload')) {
            Log::debug('CambioService: Frankfurter payload', ['body' => $res->json(), 'url' => $url]);
        }
        if (!$res->ok()) {
            Log::warning('CambioService: Frankfurter não OK', ['status' => $res->status(), 'url' => $url]);
            return null;
        }
        $body = $res->json();
        $rate = $body['rates']['BRL'] ?? null;
        $dateUsed = $body['date'] ?? $data;
        if (!$rate) {
            Log::info('CambioService: Frankfurter sem rate BRL', ['url' => $url, 'body' => config('cambio.log_payload') ? $body : 'hidden']);
            return null;
        }
        return [
            'valor' => (float) $rate,
            'codigo' => $codigo,
            'data_utilizada' => $dateUsed,
            'provider' => 'frankfurter',
        ];
    }

    /**
     * Resolve o código ISO da moeda a partir do nome comum ou do próprio código.
     */
    public function resolverCodigoMoeda(string $nomeOuCodigo): string
    {
        $t = trim(mb_strtoupper($nomeOuCodigo));

        // Se já parece um código de 3 letras
        if (preg_match('/^[A-Z]{3}$/', $t)) {
            return $t;
        }

        // Normalizações simples
        $t = str_replace(['Á','Â','Ã','À','É','Ê','Í','Ó','Ô','Õ','Ú','Ç'], ['A','A','A','A','E','E','I','O','O','O','U','C'], $t);

        $map = [
            'DOLAR' => 'USD',
            'DOLAR AMERICANO' => 'USD',
            'DOLAR EUA' => 'USD',
            'EURO' => 'EUR',
            'LIBRA' => 'GBP',
            'LIBRA ESTERLINA' => 'GBP',
            'PESO ARGENTINO' => 'ARS',
            'PESO MEXICANO' => 'MXN',
            'DOLAR CANADENSE' => 'CAD',
            'DOLAR AUSTRALIANO' => 'AUD',
            'IEN' => 'JPY',
            'IENE' => 'JPY',
            'FRANCO SUICO' => 'CHF',
            'YUAN' => 'CNY',
            'RENMINBI' => 'CNY',
            'REAL' => 'BRL',
        ];

        if (isset($map[$t])) {
            return $map[$t];
        }

        // Heurística por palavras-chave
        if (str_contains($t, 'DOLAR')) return 'USD';
        if (str_contains($t, 'EURO')) return 'EUR';
        if (str_contains($t, 'LIBRA')) return 'GBP';

        // Padrão: devolve BRL se não identificar (evita erro)
        return 'BRL';
    }
}
