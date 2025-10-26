<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RadioLiveprfService
{
    /**
     * Busca informações atuais da trilha via CentovaCast (streaminfo.get)
     * Retorna um array estável:
     * [ 'song' => ?string, 'artist' => ?string, 'next' => ?string, 'updated_at' => ?string, 'source' => 'centovacast', 'reason' => ?string, 'raw' => mixed ]
     */
    public function getStreamInfo(): array
    {
        $base = rtrim((string) config('services.centovacast.base_url'), '/') . '/';
        $mount = (string) config('services.centovacast.mount', 'liveprf');
        $timeout = (int) config('services.centovacast.http_timeout', 8);
        $connectTimeout = (int) config('services.centovacast.http_connect_timeout', 5);

        $url = $base . 'rpc/' . $mount . '/streaminfo.get';

        try {
            $resp = Http::timeout($timeout)
                ->connectTimeout($connectTimeout)
                ->acceptJson()
                ->post($url, []);

            if (!$resp->successful()) {
                return [
                    'song' => null,
                    'artist' => null,
                    'next' => null,
                    'updated_at' => null,
                    'source' => 'centovacast',
                    'reason' => 'http_error_' . $resp->status(),
                    'raw' => [ 'status' => $resp->status(), 'body' => $this->limitStr($resp->body()) ],
                ];
            }

            $json = $resp->json();
            // Estrutura típica: { type: 'success', data: [ { song: 'Artist - Title', track: {...}, ... } ] }
            $data = is_array($json) && isset($json['data'][0]) && is_array($json['data'][0]) ? $json['data'][0] : null;
            if (!$data) {
                return [
                    'song' => null,
                    'artist' => null,
                    'next' => null,
                    'updated_at' => now()->toIso8601String(),
                    'source' => 'centovacast',
                    'reason' => 'no_data',
                    'raw' => $json,
                ];
            }

            $song = $data['song'] ?? null;
            $artist = null;
            if (!empty($data['track']) && is_array($data['track'])) {
                $artist = $data['track']['artist'] ?? null;
            } else {
                // Tentativa de split "Artist - Title"
                if (is_string($song) && str_contains($song, ' - ')) {
                    $artist = explode(' - ', $song, 2)[0];
                }
            }

            $next = $data['next'] ?? ($data['nextsong'] ?? ($data['track']['nexttitle'] ?? null));

            return [
                'song' => $song,
                'artist' => $artist,
                'next' => $next,
                'updated_at' => now()->toIso8601String(),
                'source' => 'centovacast',
                'reason' => null,
                'raw' => $json,
            ];
        } catch (\Throwable $e) {
            Log::warning('CentovaCast streaminfo error: ' . $e->getMessage());
            return [
                'song' => null,
                'artist' => null,
                'next' => null,
                'updated_at' => now()->toIso8601String(),
                'source' => 'centovacast',
                'reason' => 'network_error',
                'raw' => [ 'error' => $e->getMessage() ],
            ];
        }
    }

    /**
     * Busca faixas recentes via CentovaCast (recenttracks.get)
     * Retorno:
     * [ 'items' => [ { 'title','description','image_url','link','timestamp' }... ], 'updated_at','source','reason','raw']
     */
    public function getRecentTracks(): array
    {
        $base = rtrim((string) config('services.centovacast.base_url'), '/') . '/';
        $mount = (string) config('services.centovacast.mount', 'liveprf');
        $timeout = (int) config('services.centovacast.http_timeout', 8);
        $connectTimeout = (int) config('services.centovacast.http_connect_timeout', 5);

        $url = $base . 'external/rpc.php';
        try {
            $resp = Http::timeout($timeout)
                ->connectTimeout($connectTimeout)
                ->acceptJson()
                ->get($url, [
                    'm' => 'recenttracks.get',
                    'username' => $mount,
                ]);

            if (!$resp->successful()) {
                return [
                    'items' => [],
                    'updated_at' => now()->toIso8601String(),
                    'source' => 'centovacast',
                    'reason' => 'http_error_' . $resp->status(),
                    'raw' => [ 'status' => $resp->status(), 'body' => $this->limitStr($resp->body()) ],
                ];
            }
            $json = $resp->json();
            // Estrutura esperada em jsonp normalizado: { type, data: [ tracks, headers, labels, limit, options ] }
            $items = [];
            if (is_array($json) && isset($json['data'][0]) && is_array($json['data'][0])) {
                foreach ($json['data'][0] as $t) {
                    if (!is_array($t)) continue;
                    $items[] = [
                        'title' => $t['title'] ?? ($t['song'] ?? ''),
                        'description' => $t['artist'] ?? ($t['album'] ?? ''),
                        'image_url' => $t['image'] ?? ($t['trackimageurl'] ?? null),
                        'link' => $t['url'] ?? null,
                        'timestamp' => isset($t['time']) && is_numeric($t['time']) ? (int)$t['time'] : null,
                    ];
                }
            }
            return [
                'items' => $items,
                'updated_at' => now()->toIso8601String(),
                'source' => 'centovacast',
                'reason' => null,
                'raw' => $json,
            ];
        } catch (\Throwable $e) {
            Log::warning('CentovaCast recenttracks error: ' . $e->getMessage());
            return [
                'items' => [],
                'updated_at' => now()->toIso8601String(),
                'source' => 'centovacast',
                'reason' => 'network_error',
                'raw' => [ 'error' => $e->getMessage() ],
            ];
        }
    }

    private function limitStr(?string $s, int $limit = 512): string
    {
        if ($s === null) return '';
        return mb_strlen($s, 'UTF-8') > $limit ? mb_substr($s, 0, $limit, 'UTF-8') : $s;
    }
}
