<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CambioService
{
    /**
     * Retorna a cotação da moeda para BRL na data informada (YYYY-MM-DD)
     * @return array{valor: float, codigo: string}|null
     */
    public function cotacaoParaBRL(string $moedaNomeOuCodigo, string $data): ?array
    {
        $codigo = $this->resolverCodigoMoeda($moedaNomeOuCodigo);

        if ($codigo === 'BRL') {
            return ['valor' => 1.0, 'codigo' => 'BRL'];
        }

        $url = sprintf('https://api.exchangerate.host/%s?base=%s&symbols=BRL', urlencode($data), urlencode($codigo));

        try {
            $res = Http::timeout(8)->retry(1, 200)->get($url);
            if (!$res->ok()) {
                return null;
            }
            $body = $res->json();
            $rate = $body['rates']['BRL'] ?? null;
            if (!$rate) {
                return null;
            }
            return ['valor' => (float) $rate, 'codigo' => $codigo];
        } catch (\Throwable $e) {
            return null;
        }
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
