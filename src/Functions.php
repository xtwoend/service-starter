<?php

use Hyperf\Amqp\Producer;
use Psr\SimpleCache\CacheInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Amqp\Message\ProducerMessageInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

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
        $validator = container()->get(\Hyperf\Validation\Contract\ValidatorFactoryInterface::class);

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
    function response(): ResponseInterface
    {
        return container()->get(ResponseInterface::class);
    }
}

if (! function_exists('publish')) {
    function produce(ProducerMessageInterface $message, bool $confirm = false, int $timeout = 5): bool
    {
        $producer = container()->get(Producer::class);
        return $producer->produce($message, $confirm, $timeout);
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
