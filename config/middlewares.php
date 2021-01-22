<?php

// global middleware
return [
    'http' => [
        \App\Middleware\IpMiddleware::class,
        \App\Middleware\TraceMiddleware::class
    ]
];