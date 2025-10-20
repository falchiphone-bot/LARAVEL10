<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\MarketDataService;
use App\Services\HolidayService;
use App\Models\AssetDailyStat;
use Carbon\Carbon;

class MarketDataController extends Controller
{
    public function __construct()
    {
        // Status é público (não sensível) para permitir cache/CDN e reduzir CPU; demais exigem auth
        $this->middleware('auth')->except(['status']);
    }

    public function quote(Request $request): JsonResponse
    {
        $symbol = strtoupper(trim((string) $request->input('symbol')));
        if ($symbol === '') {
            return response()->json(['error' => 'símbolo obrigatório'], 422);
        }
        $svc = app(MarketDataService::class);
        $data = $svc->getQuote($symbol);

        // Opcional: persistir a cotação do dia em AssetDailyStat quando solicitado
        try {
            $persist = $request->boolean('persist');
            $price = isset($data['price']) && is_numeric($data['price']) ? (float)$data['price'] : null;
            if ($persist && $price !== null) {
                $today = Carbon::now()->format('Y-m-d');
                $driver = DB::getDriverName();
                if ($driver === 'sqlsrv') {
                    // Checa existência exata para YYYY-MM-DD 00:00:00
                    $existing = DB::select('SELECT [id],[p5],[p95] FROM [asset_daily_stats] WHERE [symbol]=? AND [date]=CAST(? AS DATETIME2(7))', [$symbol, $today.' 00:00:00']);
                    if (!empty($existing)) {
                        $row = (object)$existing[0];
                        $isAcc = null;
                        if (isset($row->p5) && isset($row->p95) && $row->p5 !== null && $row->p95 !== null) {
                            $isAcc = ($price >= (float)$row->p5 && $price <= (float)$row->p95);
                        }
                        DB::update('UPDATE [asset_daily_stats] SET [close_value]=?, [is_accurate]=? WHERE [id]=?', [$price, $isAcc, $row->id]);
                    } else {
                        // Inserir novo registro básico para o dia
                        DB::insert('INSERT INTO [asset_daily_stats]([symbol],[date],[mean],[median],[p5],[p95],[close_value],[is_accurate],[created_at],[updated_at]) VALUES (?,?,?,?,?,?,?,?,SYSUTCDATETIME(),SYSUTCDATETIME())', [
                            $symbol,
                            $today.' 00:00:00',
                            null,
                            null,
                            null,
                            null,
                            $price,
                            null,
                        ]);
                    }
                } else {
                    // Drivers comuns (mysql/sqlite/pgsql)
                    $stat = AssetDailyStat::where('symbol', $symbol)
                        ->whereDate('date', $today)
                        ->first();
                    if ($stat) {
                        $isAcc = null;
                        if ($stat->p5 !== null && $stat->p95 !== null) {
                            $isAcc = ($price >= (float)$stat->p5 && $price <= (float)$stat->p95);
                        }
                        $stat->update(['close_value' => $price, 'is_accurate' => $isAcc]);
                    } else {
                        AssetDailyStat::create([
                            'symbol' => $symbol,
                            'date' => Carbon::createFromFormat('Y-m-d', $today)->startOfDay(),
                            'close_value' => $price,
                            'is_accurate' => null,
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Persistência é best-effort: erros silenciosos para não impactar a resposta principal
        }
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

        // Cache de curta duração para evitar recomputar várias vezes por minuto em múltiplas telas
        $cacheKey = 'market:status:nyse:' . md5((string)($now ? $now->toIso8601String() : 'now'));
        $info = Cache::remember($cacheKey, 60, function() use ($now) {
            $svc = app(HolidayService::class);
            return $svc->marketSessionInfoNY($now);
        });

        $resp = response()->json($info);
        // Cabeçalhos de cache para o client/proxy — podem ser ajustados conforme necessidade
        $resp->headers->set('Cache-Control', 'public, max-age=30, s-maxage=30, stale-while-revalidate=30');
        return $resp;
    }
}
