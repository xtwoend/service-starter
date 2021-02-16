<?php

use Hyperf\Utils\Codec\Json;
use Psr\SimpleCache\CacheInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

if (! function_exists('dispatch')) {
    function dispatch($event, int $priority = 1)
    {
        $eventDispatcher = container()->get(EventDispatcherInterface::class);
        $eventDispatcher->dispatch($event, $priority);
    }
}

if (! function_exists('validate')) {
    function validate(array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        $validator = container()->get(ValidatorFactoryInterface::class);

        return $validator->make($data, $rules, $messages, $customAttributes);
    }
}

if (! function_exists('request')) {
    function request(): RequestInterface
    {
        return container()->get(RequestInterface::class);
    }
}

if (! function_exists('response')) {
    function response($data, int $code = 0, array $meta = []): ResponseInterface
    {
        $response = container()->get(ResponseInterface::class);
        $message = null;
        $payload = [
            'error' => $code
        ];

        if (is_string($data)) {
            $payload['message'] = $data;
            $data = null;
        }

        if ($data || is_array($data)) {
            $payload['data'] = $data;
        }

        if ($meta) {
            $payload['meta'] = $meta;
        }

        $payload = Json::encode($payload);

        return $response
                ->withStatus(200)
                ->withHeader('content-type', 'application/json')
                ->withHeader('content-length', strlen($payload))
                ->withBody(new SwooleStream($payload));
    }
}

if (! function_exists('cache')) {
    function cache()
    {
        return container()->get(CacheInterface::class);
    }
}

if (! function_exists('container')) {
    function container()
    {
        if (! ApplicationContext::hasContainer()) {
            throw new \RuntimeException('The application context lacks the container.');
        }

        return ApplicationContext::getContainer();
    }
}
