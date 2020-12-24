<?php

use Psr\Log\LogLevel;
use Hyperf\Contract\StdoutLoggerInterface;

return [
    'name'  => env('APP_NAME', 'Stater Kit'),
    'key'   => env('APP_KEY', 'loremipsum'),
    'debug' => env('APP_DEBUG', false),
    'environment' => env('APP_ENV', 'dev'),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'locale' => env('APP_LOCALE', 'en'),
    
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
    ]
];