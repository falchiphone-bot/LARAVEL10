<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WebSearchService
{
    protected array $lastMeta = [];

    public function getLastMeta(): array
    {
        return $this->lastMeta;
    }
    public function search(
        string $query,
        int $maxResults = 5,
        string $primaryProvider = 'serpapi',
        int $timeout = 8,
        array $fallbackProviders = [],
        array $cacheCfg = []
    ): array {
        $query = trim($query);
        if ($query === '') { return []; }

    $this->lastMeta = [];
    $providers = $fallbackProviders ?: [];
        if (!in_array($primaryProvider, $providers, true)) {
            array_unshift($providers, $primaryProvider);
        }
        $providers = array_values(array_filter(array_unique($providers)));
        if (empty($providers)) { $providers = [$primaryProvider]; }

        $useCache = (bool)($cacheCfg['enabled'] ?? false);
        $ttl = (int)($cacheCfg['ttl'] ?? 0);
        $prefix = (string)($cacheCfg['key_prefix'] ?? 'websearch:');
        $cacheKey = $prefix.md5(json_encode([$query, $providers, $maxResults]));
        if ($useCache && $ttl > 0) {
            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                $this->lastMeta[] = [
                    'provider' => $primaryProvider,
                    'status' => 'CACHED',
                    'error' => false,
                    'auth_error' => false,
                    'results' => count($cached),
                    'message' => 'Resultado servido do cache',
                    'cached' => true,
                ];
                return $cached;
            }
        }

        $final = [];
        foreach ($providers as $provider) {
            $remain = $maxResults - count($final);
            if ($remain <= 0) break;
            try {
                $partial = $this->runProvider($provider, $query, $remain, $timeout);
                foreach ($partial as $p) {
                    // Evita duplicados por URL
                    if (!isset($p['url']) || collect($final)->contains(fn($e)=> ($e['url'] ?? null) === $p['url'])) {
                        continue;
                    }
                    $final[] = $p;
                    if (count($final) >= $maxResults) { break; }
                }
            } catch (\Throwable $e) {
                Log::warning('WebSearch provider failed', ['provider'=>$provider, 'error'=>$e->getMessage()]);
                $this->lastMeta[] = [
                    'provider' => $provider,
                    'status' => null,
                    'error' => true,
                    'auth_error' => false,
                    'results' => 0,
                    'message' => $e->getMessage(),
                    'cached' => false,
                ];
            }
        }

        if ($useCache && $ttl > 0 && !empty($final)) {
            Cache::put($cacheKey, $final, $ttl);
        }
        // Após loop de provedores, antes do return final, poderíamos armazenar meta via container, mas manter simples aqui.
        return $final;
    }

    protected function runProvider(string $provider, string $query, int $limit, int $timeout): array
    {
        return match($provider) {
            'serpapi' => $this->runSerpApi($query, $limit, $timeout),
            'bing'    => $this->runBing($query, $limit, $timeout),
            'google_cse' => $this->runGoogleCse($query, $limit, $timeout),
            default => $this->handleUnknownProvider($provider),
        };
    }

    protected function handleUnknownProvider(string $provider): array
    {
        Log::warning('WebSearchService: provider desconhecido', ['provider' => $provider]);
        $this->lastMeta[] = [
            'provider' => $provider,
            'status' => null,
            'error' => true,
            'auth_error' => false,
            'results' => 0,
            'message' => 'Provider desconhecido (ajuste OPENAI_CHAT_WEB_PROVIDERS)',
            'cached' => false,
        ];
        return [];
    }

    protected function runSerpApi(string $query, int $limit, int $timeout): array
    {
        $apiKey = env('SERPAPI_KEY');
        if (!$apiKey) {
            Log::info('WebSearchService: serpapi sem chave');
            $this->lastMeta[] = [
                'provider' => 'serpapi',
                'status' => null,
                'error' => true,
                'auth_error' => true,
                'results' => 0,
                'message' => 'Chave ausente',
                'cached' => false,
            ];
            return [];
        }
        $resp = Http::timeout($timeout)->get('https://serpapi.com/search.json', [
            'q' => $query,
            'api_key' => $apiKey,
            'num' => $limit,
            'hl' => 'pt-BR',
        ]);
        if (!$resp->ok()) {
            Log::warning('WebSearchService: serpapi HTTP não OK', ['status'=>$resp->status(), 'body'=>mb_substr($resp->body(),0,500)]);
            $this->lastMeta[] = [
                'provider' => 'serpapi',
                'status' => $resp->status(),
                'error' => true,
                'auth_error' => $resp->status() === 401 || $resp->status() === 403,
                'results' => 0,
                'message' => 'HTTP não OK',
                'cached' => false,
            ];
            return [];
        }
        $data = $resp->json();
        $out = [];
        foreach (($data['organic_results'] ?? []) as $item) {
            if (count($out) >= $limit) break;
            $title = $item['title'] ?? '';
            $snippet = $item['snippet'] ?? ($item['snippet_highlighted_words'][0] ?? '');
            $link = $item['link'] ?? '';
            if (!$title || !$link) continue;
            $out[] = [
                'title' => $title,
                'snippet' => $snippet,
                'url' => $link,
                'provider' => 'serpapi'
            ];
        }
        $this->lastMeta[] = [
            'provider' => 'serpapi',
            'status' => 200,
            'error' => false,
            'auth_error' => false,
            'results' => count($out),
            'message' => 'OK',
            'cached' => false,
        ];
        return $out;
    }

    protected function runBing(string $query, int $limit, int $timeout): array
    {
        $key = env('BING_SEARCH_V7_KEY') ?: env('BING_SEARCH_KEY');
        if (!$key) {
            Log::info('WebSearchService: bing sem chave');
            $this->lastMeta[] = [
                'provider' => 'bing',
                'status' => null,
                'error' => true,
                'auth_error' => true,
                'results' => 0,
                'message' => 'Chave ausente',
                'cached' => false,
            ];
            return [];
        }
        $resp = Http::timeout($timeout)->withHeaders([
            'Ocp-Apim-Subscription-Key' => $key,
        ])->get('https://api.bing.microsoft.com/v7.0/search', [
            'q' => $query,
            'mkt' => 'pt-BR',
            'count' => $limit,
            'responseFilter' => 'Webpages',
        ]);
        if (!$resp->ok()) {
            Log::warning('WebSearchService: bing HTTP não OK', ['status'=>$resp->status(), 'body'=>mb_substr($resp->body(),0,500)]);
            $this->lastMeta[] = [
                'provider' => 'bing',
                'status' => $resp->status(),
                'error' => true,
                'auth_error' => in_array($resp->status(), [401,403], true),
                'results' => 0,
                'message' => 'HTTP não OK',
                'cached' => false,
            ];
            return [];
        }
        $json = $resp->json();
        $pages = $json['webPages']['value'] ?? [];
        $out = [];
        foreach ($pages as $p) {
            if (count($out) >= $limit) break;
            $title = $p['name'] ?? '';
            $snippet = $p['snippet'] ?? '';
            $link = $p['url'] ?? '';
            if (!$title || !$link) continue;
            $out[] = [
                'title' => $title,
                'snippet' => $snippet,
                'url' => $link,
                'provider' => 'bing'
            ];
        }
        $this->lastMeta[] = [
            'provider' => 'bing',
            'status' => 200,
            'error' => false,
            'auth_error' => false,
            'results' => count($out),
            'message' => 'OK',
            'cached' => false,
        ];
        return $out;
    }

    protected function runGoogleCse(string $query, int $limit, int $timeout): array
    {
        $apiKey = config('services.google.key');
        $cx = config('services.google.cx');
        // dd($apiKey, $cx);
        if (!$apiKey || !$cx) {
            Log::info('WebSearchService: google_cse sem chave ou cx');
            $this->lastMeta[] = [
                'provider' => 'google_cse',
                'status' => null,
                'error' => true,
                'auth_error' => true,
                'results' => 0,
                'message' => 'Chave ou CX ausente',
                'cached' => false,
            ];
            return [];
        }
        // Validação extra: chave placeholder ou claramente inválida
        if (!$apiKey){
            Log::warning('WebSearchService: google_cse chave parece placeholder ou curta', ['len'=>strlen($apiKey)]);
            $this->lastMeta[] = [
                'provider' => 'google_cse',
                'status' => null,
                'error' => true,
                'auth_error' => true,
                'results' => 0,
                'message' => 'Chave placeholder/curta (verifique GOOGLE_CSE_KEY)',
                'cached' => false,
            ];
            return [];
        }
        // dd($query, $limit, $timeout, $apiKey, $cx);
        $resp = Http::timeout($timeout)->get('https://www.googleapis.com/customsearch/v1', [
            'key' => $apiKey,
            'cx' => $cx,
            'q' => $query,
            'num' => min($limit, 10),
            'hl' => 'pt-BR'
        ]);
        // dd($resp->json());
           if (!$resp->ok()) {
            $bodySnippet = mb_substr($resp->body(),0,500);
            $invalidKey = str_contains($bodySnippet, 'API key not valid');
            Log::warning('WebSearchService: google_cse HTTP não OK', [
                'status'=>$resp->status(),
                'body'=>$bodySnippet,
                'invalid_key'=>$invalidKey
            ]);
            $this->lastMeta[] = [
                'provider' => 'google_cse',
                'status' => $resp->status(),
                'error' => true,
                'auth_error' => $invalidKey || in_array($resp->status(), [401,403], true),
                'results' => 0,
                'message' => $invalidKey ? 'API key inválida' : 'HTTP não OK',
                'cached' => false,
            ];
            return [];
        }
        $json = $resp->json();
        $items = $json['items'] ?? [];
        $out = [];
        foreach ($items as $it) {
            if (count($out) >= $limit) break;
            $title = $it['title'] ?? '';
            $snippet = $it['snippet'] ?? '';
            $link = $it['link'] ?? '';
            if (!$title || !$link) continue;
            $out[] = [
                'title' => $title,
                'snippet' => $snippet,
                'url' => $link,
                'provider' => 'google_cse'
            ];
        }
        $this->lastMeta[] = [
            'provider' => 'google_cse',
            'status' => 200,
            'error' => false,
            'auth_error' => false,
            'results' => count($out),
            'message' => 'OK',
            'cached' => false,
        ];
        return $out;
    }

    public function buildContext(array $results, string $preamble): ?array
    {
        if (empty($results)) { return null; }
        $lines = [];
        foreach ($results as $i => $r) {
            $lines[] = ($i+1).'. '.$r['title'].'\n'.$r['snippet'].'\nFonte: '.$r['url'];
        }
        $content = $preamble."\n\n".implode("\n\n", $lines);
        return ['role' => 'system', 'content' => $content];
    }
}
