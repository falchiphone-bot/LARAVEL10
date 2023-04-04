<?php

return [
    'client_id' => env('SICREDI_CLIENT_ID'),
    'client_secret' => env('SICREDI_CLIENT_SECRET'),
    'redirect_uri' => env('SICREDI_REDIRECT_URI'),
    'base_url' => env('SICREDI_BASE_URL', 'https://api.sicredi.com.br/'),
    'token_url' => 'https://api.sicredi.com.br/token'
];
