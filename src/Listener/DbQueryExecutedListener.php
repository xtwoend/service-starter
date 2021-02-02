<?php

namespace App\Listener;

use Hyperf\Utils\Arr;
use Hyperf\Utils\Str;
use Psr\Log\LoggerInterface;
use Hyperf\Event\Annotation\Listener;
use Psr\Container\ContainerInterface;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;

/**
 * @Listener
 */
class DbQueryExecutedListener implements ListenerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function listen(): array
    {
        return [
            QueryExecuted::class,
        ];
    }

    /**
     * @param QueryExecuted $event
     */
    public function process(object $event)
    {
        if ($event instanceof QueryExecuted) {
            $sql = $event->sql;
            if (! Arr::isAssoc($event->bindings)) {
                foreach ($event->bindings as $key => $value) {
                    if ($value instanceof \DateTime || $value instanceof \DateTimeImmutable) {
                        $value = $value->format('Y-m-d H:i:s');
                    }

                    $sql = Str::replaceFirst('?', "'{$value}'", $sql);
                }
            }

            $message = sprintf('[%s][%s]%s', $event->connectionName, $event->time, $sql);
            $this->logger->debug($message);
        }
    }
}
