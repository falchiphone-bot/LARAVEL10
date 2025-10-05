## Instruções para Agentes de IA (Laravel 10 Monólito)

Foque em padrões já implementados. Não proponha re-arquitetura ampla sem pedido explícito. Domínios: financeiro, cadastros esportivos (TANABI / VEC), fluxos WhatsApp, OpenAI, Market Data, arquivos/backup — tudo sob mesma autenticação/permissões (Spatie Permission).

### Estrutura Essencial
- `routes/web.php` (1500+ linhas): evitar duplicação. Antes de criar rota, procure por prefixo/nome. Exemplos de padrões existentes: `openai.chat`, `api.market.quote`, `asset-stats.refreshClose`.
- `app/Services`: integrações isoladas. Ex.: `MarketDataService` (fallback em cadeia, tracking de uso), `OpenAIService` (Guzzle direto), `FtpBrowserService` (listagem segura). Métodos retornam arrays simples com chaves estáveis (`symbol`, `price`, `currency`, `updated_at`, `source`, `reason`, `detail`).
- Config central em `config/market.php` (timeouts, TTL cache, chaves) e `config/openai.php` (limites e tuning de chat/search/web/attachments). Sempre usar `config()` — não hardcode.
- Helpers globais em `app/helpers.php` (autoload via composer.json). Novos helpers: função simples + idempotente.
- Exportações usam libs já instaladas (`maatwebsite/excel`, `barryvdh/laravel-dompdf`). Nome de métodos/rotas: `exportXlsx`, `exportCsv`, `exportPdf`.

### Padrões de Serviços & Falhas
- Construtores definem `GuzzleHttp\Client` com `timeout` e `connect_timeout` de config (`market.http_timeout`, `market.http_connect_timeout`).
- Fluxo de cotação em `MarketDataService::getQuote`: Yahoo RapidAPI -> Alpha Vantage -> Stooq. Histórico: Stooq -> Alpha. Cada tentativa chama `trackUsageForResult` para incrementar cache de uso.
- Falhas transitórias retornam `price => null` + `reason` (`network_error`, `rate_limit`, `api_error`, `missing_api_key`, `no_data`). Evitar lançar exceção, exceto pré-condição (ex.: ausência de API key em `OpenAIService`).
- Normalização de entrada: `strtoupper(trim($symbol))`, datas validadas via `DateTimeImmutable`.

### Cache & Performance
- Cotações: `Cache::remember('md.quote.*', ttl)` onde ttl = `config('market.quote_cache_ttl', 60)`.
- Dashboard/contadores: manter semântica de ETag e uso de Cache (ver `DashboardCache`).
- Listagens (FTP, etc.) ordenadas com ordenação natural case-insensitive.

### Rotas & Convenções
- Prefixos preservados: `/openai/*` (chat, transcribe, records), `/api/market/*` (quote, historical, usage), `asset-stats*` (resource + ações adicionais). Evitar misturar rotas públicas e autenticadas inadvertidamente — Market Data (quote/historical) é público, usage protegido.
- Recursos REST: `Route::resource()` com `parameters([...])` quando precisa renomear (`asset-stats` => `asset_stat`). Exports adicionados como rotas GET extras (`.../export-xlsx`).

### OpenAI
- `OpenAIService` usa endpoints `chat/completions`, `audio/transcriptions`. Sempre conferir `services.openai.api_key` antes; lançar `ApiKeyMissingException` se ausente.
- Config de enriquecimento de chat (busca, web, attachments) em `config/openai.php`. Reutilizar preâmbulos se gerar novos contextos.

### Market Data
- Novos provedores: criar `getQuote<Nome>()` seguindo a assinatura de retorno e adicionar na cadeia em `getQuote` ou histórico; registrar uso via `trackUsageForResult`.
- Respeitar limites/headers RapidAPI — probe opcional via `getUsageSnapshot(true)` já implementado.

### Arquivos & Storage
- Acesso a FTP/Drive via drivers configurados (`config/filesystems.php`). Nunca expor credenciais em logs ou código.
- Sanitização contra path traversal (`..`) obrigatória (seguir padrão de `FtpBrowserService`).

### Testes
- Pest instalado. Novos testes Feature: `tests/Feature/*`, usar factories e `RefreshDatabase` se mexer em modelos. Para integrações externas, mock de Client ou fakes — não realizar chamadas reais em CI.

### Segurança & Logs
- Não logar chaves/segredos. Logs externos truncam corpo (ex.: MarketData loga apenas cabeçalho inicial). Use `Log::warning` já estabelecido para anomalias de provedores.

### Extensão Segura
Ao adicionar código, valide:
1. Usa apenas configs/env (sem valores mágicos)?
2. Formato de retorno preserva chaves existentes?
3. Timeouts definidos para novas chamadas HTTP?
4. Cache aplicado quando leitura frequente ou rota de dashboard?
5. Nome/prefixo de rota consistente com padrões atuais?

### Exemplos Rápidos
- Rota existente: `Route::get('/api/market/quote', MarketDataController::class.'@quote')->name('api.market.quote');`
- Fallback de cotação: veja `MarketDataService::getQuote()` (cadeia + cache + tracking).
- Retorno padrão de falha: `['symbol'=>$sym,'price'=>null,'currency'=>null,'updated_at'=>null,'source'=>'alpha_vantage','reason'=>'rate_limit']`.

### Pitfalls Conhecidos
- Rotas duplicadas no `web.php` (verificar grep antes). 
- Stooq retorna `N/D` para `Close` — validar `is_numeric` antes de cast. 
- Alpha Vantage: mensagens em `Note` / `Information` => tratar como `rate_limit`.

Feedback: Informe lacunas ou áreas ambíguas para refinarmos este guia.
