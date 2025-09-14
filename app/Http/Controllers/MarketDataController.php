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
        // Normalizar data como YYYY-MM-DD
        try {
            $dt = new \DateTime($date);
            $date = $dt->format('Y-m-d');
        } catch (\Throwable $e) {
            return response()->json(['error' => 'data inválida (use YYYY-MM-DD)'], 422);
        }
        $svc = app(MarketDataService::class);
        $data = $svc->getHistoricalQuote($symbol, $date);
        return response()->json($data);
    }
}
