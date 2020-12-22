<?php

use App\Routing\RouteFactory;
use App\Routing\RouteRegistry;
use App\Services\RestClientFactory;
use App\Services\RestClientInterface;

return [
    RouteRegistry::class => RouteFactory::class,
];