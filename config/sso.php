<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Gates
    |--------------------------------------------------------------------------
    */
    'gates' => [
        'local' => [
            'enabled' => env('AUTH_LOCAL_ENABLED', true),
            'registration' => env('AUTH_LOCAL_REGISTRATION', false),
        ],
        'sso' => [
            'enabled' => env('AUTH_SSO_ENABLED', true),
            'force' => env('AUTH_SSO_FORCE', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SSO SIAKAD Configuration
    |--------------------------------------------------------------------------
    */
    'siakad' => [
        'base_url' => env('SIAKAD_SSO_URL', 'https://sso.siakad.unpatti.ac.id'),
        'client_id' => env('SIAKAD_SSO_CLIENT_ID'),
        'client_secret' => env('SIAKAD_SSO_CLIENT_SECRET'),
        'redirect_uri' => env('SIAKAD_SSO_REDIRECT_URI'),
        'scopes' => env('SIAKAD_SSO_SCOPES', 'openid,profile,email,role'),
        
        // Endpoints
        'authorize_endpoint' => '/oauth/authorize',
        'token_endpoint' => '/oauth/token',
        'userinfo_endpoint' => '/api/userinfo',
        'logout_endpoint' => '/logout',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Mapping from SIAKAD
    |--------------------------------------------------------------------------
    */
    'role_mapping' => [
        'mahasiswa' => 'student',
        'student' => 'student',
        'dosen' => 'Validator',
        'lecturer' => 'Validator',
        'staff' => 'Validator',
        'staff_kemahasiswaan' => 'Validator',
        'admin' => 'Admin',
        'wakil_dekan' => 'Admin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Settings
    |--------------------------------------------------------------------------
    */
    'migration' => [
        'deadline' => env('AUTH_MIGRATION_DEADLINE'),
        'notify_users' => env('AUTH_NOTIFY_MIGRATION', false),
    ],
];
