<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\MarketDataService;

class MarketDataController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function quote(Request $request): JsonResponse
    {
        $symbol = strtoupper(trim((string) $request->input('symbol')));
        if ($symbol === '') {
            return response()->json(['error' => 'símbolo obrigatório'], 422);
        }
        $svc = app(MarketDataService::class);
        $data = $svc->getQuote($symbol);
        return response()->json($data);
    }

    public function historicalQuote(Request $request): JsonResponse
    {
        $symbol = strtoupper(trim((string) $request->input('symbol')));
        $date = trim((string) $request->input('date'));
        if ($symbol === '' || $date === '') {
            return response()->json(['error' => 'símbolo e data são obrigatórios'], 422);
        }
        // Normalizar data como YYYY-MM-DD, aceitando dd/mm/yyyy e dd-mm-yyyy
        try {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
                $dt = \DateTime::createFromFormat('d/m/Y', $date);
                if (!$dt) { throw new \RuntimeException('invalid date'); }
            } elseif (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
                $dt = \DateTime::createFromFormat('d-m-Y', $date);
                if (!$dt) { throw new \RuntimeException('invalid date'); }
            } else {
                $dt = new \DateTime($date);
            }
            $date = $dt->format('Y-m-d');
        } catch (\Throwable $e) {
            return response()->json(['error' => 'data inválida (use YYYY-MM-DD ou dd/mm/aaaa)'], 422);
        }
        $svc = app(MarketDataService::class);
        $data = $svc->getHistoricalQuote($symbol, $date);
        if (!$data || ($data['price'] ?? null) === null || !($data['date'] ?? null)) {
            // Indicar falha de dados (ex.: limite do Stooq atingido ou Alpha Vantage sem chave)
            $src = $data['source'] ?? null;
            $msg = 'Sem dados para a data informada';
            if ($src === 'stooq') { $msg .= ' (fonte Stooq possivelmente com limite diário atingido)'; }
            if ($src === 'alpha_vantage') { $msg .= ' (Alpha Vantage sem chave ou sem dados)'; }
            return response()->json(['error' => $msg, 'source' => $src], 404);
        }
        return response()->json($data);
    }
}
