## Instruções para Agentes de IA neste Repositório Laravel 10

Foque em decisões já consolidadas no código. Evite sugerir reestruturações amplas sem pedido explícito. Este app é um monolito Laravel 10 com múltiplos domínios funcionais (financeiro, cadastros esportivos TANABI/vec, WhatsApp flows, OpenAI, Market Data, backups/arquivos) compartilhando autenticação e permissões.

### Arquitetura & Pastas-Chave
- `app/Services`: lógica externa ou agregadora (ex: `MarketDataService`, `OpenAIService`, `FtpBrowserService`). Padrões: construtor injeta ou cria `GuzzleHttp\Client`; métodos retornam arrays estruturados explícitos. Preserve chaves existentes (`symbol`, `price`, `source`, `reason`, etc.).
- `routes/web.php`: concentra MUITAS rotas (mais de 1500 linhas). Não duplique rotas; ao adicionar, considere extrair futuro módulo somente se solicitado. Rotas OpenAI, Market Data e FTP têm nomes consistentes (prefixo `openai.*`, `asset-stats.*`, `ftp.*`, etc.).
- `config/market.php`, `config/openai.php`, `config/filesystems.php`: fornecem chaves de configuração usadas por serviços; sempre usar `config()` em vez de constantes ao acessar parâmetros.
- `app/helpers.php`: funções utilitárias simples (CPF/CNPJ/email). Adicionar novo helper: seguir padrão procedural e registrar em `composer.json` (autoload.files) se precisar carga global.
- `app/Services/DashboardCache.php`: (não lido aqui, mas usado intensivamente em `/dashboard/counts`). Ao alterar contadores, manter uso de Cache e ETag retornada no endpoint.
- `storage/` + discos: manipulação de arquivos usa drivers Laravel; FTP configurado em `filesystems.php` (não coloque credenciais em código).

### Padrões de Serviços
- Tratamento de falhas: capturar exceções e retornar estrutura com `price => null` + campos `reason`/`detail` quando aplicável (vide `MarketDataService`). Não lançar exceção para falhas transitórias de provedores externos, exceto casos explícitos (ex: falta de chave em `OpenAIService`).
- Cache: usar `Cache::remember` com TTL vindo de config (ex: `quote_cache_ttl`). Não codificar TTL fixo sem justificativa.
- Normalização de símbolos/inputs: converter para maiúsculo (`strtoupper`) ou limpar (`trim`) antes de processar.

### Rotas & Nomenclatura
- Rotas REST principais usam `Route::resource` e nomes explicitamente sobrepostos quando necessário para manter letra maiúscula (ex: `Pix`, `FormaPagamento`). Preserve consistência ao criar novos exports (`export`, `exportXlsx`, `exportPdf`, `exportCsv`).
- Endpoints internos de diagnóstico / health usam JSON enxuto (`/healthz`, `/dashboard/counts`). Se adicionar campos, não quebrar chaves existentes.
- Ao adicionar rota OpenAI/Market Data, reutilize prefixos existentes (`/openai/...`, `/api/market/...`). Decisões: Market Data público (alguns endpoints fora do middleware auth), usage permanece protegido.

### Integrações Externas
- OpenAI: `OpenAIService` usa Guzzle direto (não SDK). Autorização via header Bearer; erros de rede mapeados para `NetworkException`. Novos métodos devem seguir padrão de payload e JSON decode atual.
- Market Data: múltiplos provedores (Yahoo via RapidAPI, Alpha Vantage, Stooq). Ordem de fallback: Yahoo -> Alpha -> Stooq em `getQuote`; Stooq -> Alpha em histórico. Respeitar contadores (`trackUsageForResult`). Qualquer novo provedor: implementar contagem de uso e campos `source`, `reason`.
- FTP: Navegação via `FtpBrowserService::list`, cuidando de sanitização contra `..`. Ao expandir, manter retorno com arrays ordenados alfabeticamente.
- Google Drive: driver custom em `filesystems` usa variáveis de ambiente; não hardcode tokens.

### Performance & Confiabilidade
- Evitar loops de rede sem timeout: sempre defina `timeout` e `connect_timeout` conforme config.
- Funções que podem rodar em dashboard devem usar cache agressivo (vide contadores, market quotes).
- Para listas grandes (FTP, registros), ordenar natural case-insensitive (`SORT_NATURAL | SORT_FLAG_CASE`).

### Fluxo de Trabalho de Desenvolvimento
- Dependências PHP: usar Composer (PHP 8.1+). Scripts pós-instalação já geram `.env` e `artisan key:generate` se ausente.
- Front-end: Vite + Tailwind. Rodar `npm run dev` ou `npm run build` conforme necessário; não adicionar ferramentas front-end pesadas sem justificativa.
- Testes: Pest + `RefreshDatabase` aplicado a testes Feature. Novo teste Feature deve residir em `tests/Feature` e usar factories. Não misturar lógica de integração externa sem mocks.

### Convenções de Código
- Arrays de retorno de serviços são associativos simples para facilitar serialização; evitar DTOs complexos sem demanda.
- Reutilize helpers de formatação existentes (ex: `humanBytes` em FTP) ou extraia para método privado consistente.
- Campos booleanos/flags via env: usar funções anônimas ou coerção já vista em configs para normalizar valores.

### Erros & Tratamento
- Exceções somente para erros de pré-condição (ex: chave ausente, arquivo não encontrado local). Falhas de provedores externos retornam estrutura nula + `reason` para permitir UI degradar.
- Ao adicionar novas razões, escolha slug curto (`rate_limit`, `network_error`, `api_error`, `missing_api_key`, `no_data`).

### Segurança & Privacidade
- Nunca logar chaves API. Logs de provedores (MarketData / OpenAI) usam mensagens curtas; truncar corpo de resposta grande.
- Evitar inserir dados sensíveis em exceções propagadas.

### Como Estender
- Novo provedor de cotação: criar método `getQuote<Provider>()` (padrão de retorno) + integrar na cadeia em `getQuote` respeitando fallback e `trackUsageForResult`.
- Nova funcionalidade OpenAI (ex: embeddings): seguir padrão de autenticação e lançar `ApiKeyMissingException` se ausente.
- Novos endpoints de exportação: preferir geração streaming ou libs já presentes (`maatwebsite/excel`, `dompdf`). Usar nomes `exportXlsx`, `exportCsv`, `exportPdf`.

### Pitfalls Já Observados
- Rotas duplicadas podem ocorrer facilmente dado tamanho de `web.php`; antes de criar uma similar, buscar por nome/prefixo.
- Cotações Stooq podem retornar `N/D`; validar sempre antes de converter.
- Limites Alpha Vantage retornam mensagens em `Note` ou `Information`; tratar como `rate_limit`.

### Checklist ao Criar Código Novo
1. Usa config/env em vez de valores mágicos?
2. Retornos seguem estrutura existente (chaves esperadas)?
3. Timeouts definidos para chamadas externas?
4. Cache aplicado onde leitura é frequente?
5. Nome de rota segue padrão de prefixo atual?

Feedback: Se algo aqui não refletir o padrão real ou faltar contexto, relate para ajuste incremental em próximas iterações.
