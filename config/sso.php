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
    | SSO Configuration
    |--------------------------------------------------------------------------
    */
    'base_url' => env('SSO_BASE_URL', 'https://sso.unpatti.ac.id'),
    'client_id' => env('SSO_CLIENT_ID'),
    'client_secret' => env('SSO_CLIENT_SECRET'),
    'redirect_uri' => env('SSO_REDIRECT_URI', env('APP_URL') . '/sso/callback'),

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
