<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\MarketDataService;
use App\Services\HolidayService;
use Carbon\Carbon;

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
            $src = $data['source'] ?? null;
            $reason = $data['reason'] ?? 'no_data';
            $detail = $data['detail'] ?? null;
            $status = $reason === 'rate_limit' ? 429 : 404;
            $msg = 'Sem dados para a data informada';
            if ($reason === 'rate_limit') { $msg = 'Limite de uso atingido na fonte'; }
            if ($reason === 'missing_api_key') { $msg = 'Chave de API ausente para a fonte'; }
            if ($reason === 'api_error') { $msg = 'Erro na API da fonte'; }
            $payload = ['error' => $msg, 'source' => $src, 'reason' => $reason];
            if ($detail) { $payload['detail'] = $detail; }
            return response()->json($payload, $status);
        }
        return response()->json($data);
    }

    public function usage(Request $request): JsonResponse
    {
        $probe = (bool) $request->boolean('probe');
        $svc = app(MarketDataService::class);
        $snap = $svc->getUsageSnapshot($probe);
        return response()->json($snap);
    }

    /**
     * Status da sessão do mercado (NYSE): pré-mercado, aberto, after-hours, fechado.
     * Parâmetro opcional: at=YYYY-MM-DD HH:mm:ss (assume timezone local do servidor) ou ISO8601.
     */
    public function status(Request $request): JsonResponse
    {
        $now = null;
        $at = $request->input('at');
        if (is_string($at) && trim($at) !== '') {
            try { $now = new Carbon($at); } catch (\Throwable $e) { $now = null; }
        }
        $svc = app(HolidayService::class);
        $info = $svc->marketSessionInfoNY($now);
        return response()->json($info);
    }
}
