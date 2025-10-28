<?php

return [
    // Limite máximo de registros ao usar modo "Listar tudo" em asset-stats (proteção memória)
    'asset_stats_max_all' => env('ASSET_STATS_MAX_ALL', 20000),
    // Configurações da tela de ativos (records/assets)
    'assets' => [
        // Intervalo padrão (ms) para execução automática do "Consultar todos" (AUTO)
        // Pode ser alterado via .env OPENAI_ASSETS_AUTO_BATCH_INTERVAL_MS
        'auto_batch_interval_default_ms' => env('OPENAI_ASSETS_AUTO_BATCH_INTERVAL_MS', 120000), // 2 minutos
    ],
    // Configurações específicas da tela de registros (openai/records)
    'records' => [
        // Intervalo padrão (ms) entre passos do modo "Auto variações (sequencial)"
        // Pode ser alterado via .env OPENAI_RECORDS_AUTO_SEQ_INTERVAL_MS
        'auto_var_seq_interval_default_ms' => env('OPENAI_RECORDS_AUTO_SEQ_INTERVAL_MS', 120000),
        // Temporizador regressivo em loop (ms) que reinicia automaticamente enquanto o modo sequencial estiver ativo
        // Usado apenas para feedback visual (não agenda execuções). Mantém separado para não conflitar com outros fluxos
        // Pode ser alterado via .env OPENAI_RECORDS_AUTO_SEQ_LOOP_MS
        'auto_var_seq_loop_default_ms' => env('OPENAI_RECORDS_AUTO_SEQ_LOOP_MS', 1000),
    ],
    'chat' => [
        'search' => [
            // Habilitado por checkbox no formulário; isto define apenas o padrão se você quiser usar em outro lugar
            'enabled_default' => env('OPENAI_CHAT_SEARCH_ENABLED_DEFAULT', true),
            // Quantidade máxima de termos extraídos do prompt para busca
            'max_terms' => env('OPENAI_CHAT_SEARCH_MAX_TERMS', 5),
            // Tamanho mínimo do termo (nº de caracteres)
            'min_term_length' => env('OPENAI_CHAT_SEARCH_MIN_TERM_LENGTH', 4),
            // Quantas conversas consultar no banco (top-N mais recentes com hit)
            'max_conversations_to_query' => env('OPENAI_CHAT_SEARCH_MAX_QUERY', 5),
            // Quantas conversas injetar no contexto enviado à IA
            'max_conversations_to_inject' => env('OPENAI_CHAT_SEARCH_MAX_INJECT', 3),
            // Quantas mensagens recentes extrair de cada conversa encontrada
            'tail_messages_per_conversation' => env('OPENAI_CHAT_SEARCH_TAIL_PER_CONV', 6),
            // Permitir buscar em todas as conversas (não só do usuário atual)
            'allow_all' => env('OPENAI_CHAT_SEARCH_ALLOW_ALL', true),
            // Se definido, exige essa permissão para usar escopo "todas"
            'allow_all_permission' => env('OPENAI_CHAT_SEARCH_ALLOW_ALL_PERMISSION', null),
            // Texto do preâmbulo do contexto injetado
            'context_preamble' => env('OPENAI_CHAT_SEARCH_CONTEXT_PREAMBLE', 'Use rigorosamente o contexto abaixo para identificar pessoas, relações e fatos. Se a resposta estiver no contexto, priorize-o. Quando o contexto conflitar com conhecimento geral, prefira o contexto. Se não houver evidência no contexto, responda de forma conservadora sem inventar.'),
            // Collation para busca acento-insensível (SQL Server). Ex.: Latin1_General_CI_AI
            'collation' => env('OPENAI_CHAT_SEARCH_COLLATION', 'Latin1_General_CI_AI'),
        ],
        'attachments' => [
            // Habilitar injeção de conteúdo de anexos no contexto do chat
            'enabled' => env('OPENAI_CHAT_ATTACHMENTS_ENABLED', true),
            // Máximo de anexos a processar por mensagem
            'max_files' => env('OPENAI_CHAT_ATTACHMENTS_MAX_FILES', 3),
            // Tamanho máximo de caracteres por arquivo
            'max_chars_per_file' => env('OPENAI_CHAT_ATTACHMENTS_MAX_CHARS_PER_FILE', 20000),
            // Tamanho máximo total de caracteres somando todos os anexos
            'max_total_chars' => env('OPENAI_CHAT_ATTACHMENTS_MAX_TOTAL_CHARS', 40000),
            // Preambulo para orientar a IA
            'context_preamble' => env('OPENAI_CHAT_ATTACHMENTS_CONTEXT_PREAMBLE', 'Use o conteúdo dos anexos abaixo como fonte primária para responder. Se a pergunta estiver coberta pelos anexos, priorize-os. Não invente informações que não estejam neles.'),
        ],
        'web' => [
            // Habilitar recurso de busca web para enriquecer a resposta
            'enabled' => env('OPENAI_CHAT_WEB_ENABLED', true),
            // Se deve vir marcado por padrão
            'default_enabled' => env('OPENAI_CHAT_WEB_DEFAULT', false),
            // Provedor principal (legacy). Mantido p/ retrocompatibilidade.
            'provider' => env('OPENAI_CHAT_WEB_PROVIDER', 'serpapi'),
            // Lista de provedores em ordem de tentativa. Suporta: serpapi, bing, google_cse
            // Se definida, sobrescreve 'provider'. Ex: "serpapi,bing" ou só "bing"
            'providers' => array_filter(array_map('trim', explode(',', env('OPENAI_CHAT_WEB_PROVIDERS', '')))),
            // Máximo de resultados agregados finais
            'max_results' => env('OPENAI_CHAT_WEB_MAX_RESULTS', 5),
            // Timeout individual por requisição HTTP (segundos)
            'timeout' => env('OPENAI_CHAT_WEB_TIMEOUT', 8),
            // Preambulo contextual a ser injetado antes dos resultados
            'preamble' => env('OPENAI_CHAT_WEB_PREAMBLE', 'Resultados recentes da web (não verificados). Use-os para complementar a resposta. Cite a fonte (URL) quando fizer afirmações baseadas neles. Ignore se irrelevantes.'),
            // Cache: habilitar e TTL em segundos (0 = desabilita)
            'cache' => [
                'enabled' => env('OPENAI_CHAT_WEB_CACHE_ENABLED', true),
                'ttl' => env('OPENAI_CHAT_WEB_CACHE_TTL', 600), // 10 min padrão
                'key_prefix' => env('OPENAI_CHAT_WEB_CACHE_PREFIX', 'websearch:'),
            ],
        ],
    ],
];
