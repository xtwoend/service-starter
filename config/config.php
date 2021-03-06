<?php

use Psr\Log\LogLevel;
use Hyperf\Contract\StdoutLoggerInterface;

return [
    'app_env' => env('APP_ENV', 'dev'),
    'name'  => env('APP_NAME', 'Stater Kit'),
    'key'   => env('APP_KEY', 'loremipsum'),
    'debug' => env('APP_DEBUG', false),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => env('APP_LOCALE', 'en'),
    'scan_cacheable' => env('SCAN_CACHEABLE', false),

    // stdout logger
    StdoutLoggerInterface::class => [
        'log_level' => [
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::DEBUG,
            LogLevel::EMERGENCY,
            LogLevel::ERROR,
            LogLevel::INFO,
            LogLevel::NOTICE,
            LogLevel::WARNING,
        ],
    ],
    'aspects' => [
        // 
    ],
    'annotations' => [
        'scan' => [
            'paths' => [
                BASE_PATH . '/src',
            ],
            'ignore_annotations' => [
                'mixin',
            ],
        ],
    ]
];