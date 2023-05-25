<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sicredi' => [
        'client_id' => env('SICREDI_CLIENT_ID'),
        'secret_id' => env('SICREDI_CLIENT_SECRET'),
        'token' => env('SICREDI_TOKEN'),
        'url' => env('SICRED_URL'),

    ],

    'google_drive' => [
        'folder' => env('FOLDER_DRIVE_GOOGLE')
    ],

    'google' => [
        // 'client_id' => '276920584995-dh9gflphucm37l3rrh1md4i3jaerrcbv.apps.googleusercontent.com',
        // 'client_secret' => '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',
        'client_id' => env('GOOGLE_API_CLIENT_ID'),
        'client_secret' => env('GOOGLE_API_CLIENT_SECRET'),
        // 'redirect' => 'http://localhost:82/auth/google/callback',
        'redirect' => 'https://contabilidade.falchi.com.br/auth/google/callback',
    ],

];
