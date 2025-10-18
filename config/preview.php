<?php

return [
    // Tempo padrão de vida do cache da pré-visualização de despesas, em segundos.
    // Ex.: 3600 = 1 hora, 86400 = 24 horas, 604800 = 7 dias.
    'cache_ttl_seconds' => env('PREVIEW_CACHE_TTL_SECONDS', 3600),

    // Quando true, usa Cache::forever() e o cache não expira automaticamente.
    // Útil enquanto estiver trabalhando longamente numa mesma prévia.
    'cache_forever' => env('PREVIEW_CACHE_FOREVER', false),
];
