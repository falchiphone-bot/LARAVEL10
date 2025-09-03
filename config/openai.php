<?php

return [
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
    ],
];
