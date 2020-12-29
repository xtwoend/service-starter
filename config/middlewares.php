<?php

// global middleware
return [
    'http' => [
        \App\Middleware\IpMiddleware::class
    ]
];