<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SIKAD API Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk integrasi dengan Sistem Informasi Akademik (SIKAD)
    | Universitas Pattimura
    |
    */

    // Base URL untuk SIKAD API
    'base_url' => env('SIKAD_API_URL', 'https://sikad.unpatti.ac.id/api'),

    // API Key untuk autentikasi
    'api_key' => env('SIKAD_API_KEY', ''),

    // Timeout untuk request (dalam detik)
    'timeout' => env('SIKAD_TIMEOUT', 30),

    // Enable/disable SIKAD integration
    'enabled' => env('SIKAD_ENABLED', true),

    // Auto-sync saat login
    'auto_sync_on_login' => env('SIKAD_AUTO_SYNC', true),

    // Cache duration untuk data mahasiswa (dalam menit)
    'cache_duration' => env('SIKAD_CACHE_DURATION', 60),

    // Retry configuration
    'retry' => [
        'times' => 3,
        'sleep' => 100, // milliseconds
    ],

    // Logging
    'log_requests' => env('SIKAD_LOG_REQUESTS', true),
];
