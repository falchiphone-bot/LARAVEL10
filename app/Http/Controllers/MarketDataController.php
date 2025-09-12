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
}
