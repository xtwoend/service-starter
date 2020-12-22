<?php

return [
    'router' => [
        'cache' => true,
        'cache_lifetime' => 30 * 1000
    ],
    'logger' => [
        'enable' => true
    ],
    'http' => [
        'timeout' => 1,
        'max_connection' => 1000,
        'retries' => 1,
        'delay' => 0
    ]
];