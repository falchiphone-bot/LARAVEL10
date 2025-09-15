<?php

return [
    // Quantos dias voltar no timeseries quando não houver cotação na data solicitada
    'timeseries_lookback_days' => env('CAMBIO_LOOKBACK_DAYS', 14),

    // Logar o payload completo das respostas das APIs (cuidado em produção)
    'log_payload' => env('CAMBIO_LOG_PAYLOAD', false),

    // Desabilitar verificação SSL (apenas para diagnóstico!)
    'insecure' => env('CAMBIO_INSECURE', false),
];
