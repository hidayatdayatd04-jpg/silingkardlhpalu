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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'gps' => [
        'login_url' => env('GPS_LOGIN_URL', 'https://portal.gps.id/backend/api/single_login'),
        'monitoring_url' => env('GPS_MONITORING_URL', 'https://portal.gps.id/backend/seen/gps/list_monitoring'),
        'username' => env('GPS_USERNAME', ''),
        'password' => env('GPS_PASSWORD', ''),
        'default_lat' => env('GPS_PALU_DEFAULT_LAT', -0.9),
        'default_lng' => env('GPS_PALU_DEFAULT_LNG', 119.87),
    ],

    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY', ''),
        'model' => env('OPENROUTER_MODEL', 'tencent/hy3:free'),
    ],

    'google_drive' => [
        'api_key' => env('GOOGLE_DRIVE_API_KEY', ''),
        'tata_lingkungan_folder_id' => env('GOOGLE_DRIVE_TATA_LINGKUNGAN_FOLDER_ID', '14yIelP7BkmVw7bTOl-cIyNYI1jRmLvsQ'),
        'cache_ttl' => (int) env('GOOGLE_DRIVE_CACHE_TTL', 900),
        'max_depth' => (int) env('GOOGLE_DRIVE_MAX_DEPTH', 8),
    ],

];
