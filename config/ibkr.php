<?php

return [
    // Base URL para a Web API da IBKR (organizações). Ajuste via ENV se necessário.
    // Ex.: https://api.ibkr.com
    'base_url' => env('IBKR_BASE_URL', 'https://api.ibkr.com'),

    // Endpoints OAuth (podem variar por ambiente/região; sobrescreva via ENV se necessário)
    'oauth_authorize_url' => env('IBKR_OAUTH_AUTHORIZE_URL', 'https://api.ibkr.com/v1/api/oauth/authorize'),
    'oauth_token_url'     => env('IBKR_OAUTH_TOKEN_URL', 'https://api.ibkr.com/v1/api/oauth/token'),

    // Credenciais da aplicação (Organizações)
    'client_id' => env('IBKR_CLIENT_ID'),
    'client_secret' => env('IBKR_CLIENT_SECRET'),
    'redirect_uri' => env('IBKR_REDIRECT_URI', env('APP_URL').'/ibkr/callback'),

    // Escopos OAuth solicitados (ajuste conforme necessidade/contrato IBKR)
    // A IBKR define escopos por produto/recurso. Use espaço como separador ao montar a URL.
    'scopes' => explode(' ', env('IBKR_SCOPES', 'read')),

    // Timeouts HTTP
    'http_timeout' => (float) env('IBKR_HTTP_TIMEOUT', 10.0),
    'http_connect_timeout' => (float) env('IBKR_HTTP_CONNECT_TIMEOUT', 5.0),
];
