<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    // 'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // 'allowed_methods' => ['*', 'PUT'],

    // 'allowed_origins' => ['https://*marstaan.com', 'https://*.marstaan.com', 'https://clinic.marstaan.com', 'https://*.marstaan.com/*', 'http://127.0.0.1:5173', 'https://clinic.marstaan.com/*', 'https://boring-payne.212-227-188-105.plesk.page', 'https://boring-payne.212-227-188-105.plesk.page/*', 'https://clinic.marstaan.com/auth/login'],
    // // 'allowed_origins' => ['*'],

    // // 'allowed_origins_patterns' => ['/(*.marstaan)\.com'],
    // 'allowed_origins_patterns' => ['*'],

    // 'allowed_headers' => ['*'],

    // 'exposed_headers' => [],

    // 'max_age' => 0,

    // 'supports_credentials' => true,

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
