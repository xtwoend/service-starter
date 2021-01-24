<?php

use Zipkin\Samplers\BinarySampler;

return [
    'default' => env('TRACER_DRIVER', 'zipkin'),
    'enable' => [
        'guzzle' => (bool) env('TRACER_ENABLE_GUZZLE', false), // not working require aop
        'redis' => (bool) env('TRACER_ENABLE_REDIS', false), // not working require aop
        'db' => (bool) env('TRACER_ENABLE_DB', false), // work with Listener
        'method' => (bool) env('TRACER_ENABLE_METHOD', false), // not workin require aop
    ],
    'tracer' => [
        'zipkin' => [
            'driver' => Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
            'app' => [
                'name' => env('APP_NAME', 'skeleton'),
                'ipv4' => env('APP_HOST'),
                'ipv6' => null,
                'port' => (int) env('APP_PORT'),
            ],
            'options' => [
                'endpoint_url' => env('ZIPKIN_ENDPOINT_URL', 'http://localhost:9411/api/v2/spans'),
                'timeout' => (bool) env('ZIPKIN_TIMEOUT', 1),
            ],
            'sampler' => BinarySampler::createAsAlwaysSample(),
        ]
    ],
    'tags' => [
        'http_client' => [
            'http.url' => 'http.url',
            'http.method' => 'http.method',
            'http.status_code' => 'http.status_code',
            'http.response' => 'http.response'
        ],
        'redis' => [
            'arguments' => 'arguments',
            'result' => 'result',
        ],
        'db' => [
            'db.query' => 'db.query',
            'db.statement' => 'db.statement',
            'db.query_time' => 'db.query_time',
        ],
    ],
];