<?php

use Monolog\Handler\StreamHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('APP_LOG', 'single'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
        ],

        'single' => [
            'driver' => env('APP_LOG', 'single'),
            'path' => storage_path('logs/laravel.log'),
            'level' => env('APP_LOG_LEVEL', 'debug'),
        ],
        'zoom' => [
            'driver' => env('APP_LOG', 'single'),
            'path' => storage_path('logs/zoom_log.log'),
            'level' => env('APP_LOG_LEVEL', 'debug'),
        ],
        'chat' => [
            'driver' => env('APP_LOG', 'single'),
            'path' => storage_path('logs/chat_log.log'),
            'level' => env('APP_LOG_LEVEL', 'debug'),
        ],
        'stc_events' => [
            'driver' => env('APP_LOG', 'single'),
            'path' => storage_path('logs/stc_events/'.time().'.log'),
            'level' => env('APP_LOG_LEVEL', 'debug'),
        ],
        'microsoft_teams' => [
            'driver' => env('APP_LOG', 'single'),
            'path' => storage_path('logs/microsoft_teams.log'),
            'level' => env('APP_LOG_LEVEL', 'debug'),
        ],
        'notifications' => [
            'driver' => env('APP_LOG', 'single'),
            'path' => storage_path('logs/notifications.log'),
            'level' => env('APP_LOG_LEVEL', 'debug'),
        ],
        'signature' => [
            'driver' => env('APP_LOG', 'single'),
            'path' => storage_path('logs/signature_log.log'),
            'level' => env('APP_LOG_LEVEL', 'debug'),
        ],
        'sms' => [
            'driver' => env('APP_LOG', 'single'),
            'path' => storage_path('logs/sms_log.log'),
            'level' => env('APP_LOG_LEVEL', 'debug'),
        ],
        'ldap' => [
            'driver' => env('APP_LOG', 'single'),
            'path' => storage_path('logs/ldap_log-'.time().'.log'),
            'level' => env('APP_LOG_LEVEL', 'info'),
        ],
    ],

];