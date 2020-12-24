<?php

namespace App\Exception;

use Throwable;
use OpenTracing\Tracer;
use Hyperf\Utils\Coroutine;
use Hyperf\Utils\Codec\Json;
use Hyperf\Tracer\SpanStarter;
use Psr\Http\Message\ResponseInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Exception\NotFoundHttpException;
use Hyperf\HttpMessage\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    use SpanStarter;
    
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var Tracer
     */
    private $tracer;

    public function __construct(StdoutLoggerInterface $logger, Tracer $tracer)
    {
        $this->logger = $logger;
        $this->tracer = $tracer;
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->logger->error(
            sprintf('%s[%s] in %s', 
                $throwable->getMessage(), 
                $throwable->getLine(), $throwable->getFile()
            )
        );

        $this->logger->error($throwable->getTraceAsString());

        $statusCode = $this->isHttpException($throwable)? $throwable->getStatusCode(): 500;
        $error = $this->convertExceptionToArray($throwable);

        $this->sendError($error);

        return $response
            ->withHeader('Server', 'Hyperf')
            ->withStatus($statusCode)
            ->withAddedHeader('content-type', 'application/json')
            ->withBody(new SwooleStream(Json::encode($error)));
    }

    /**
     * Undocumented function
     *
     * @param Throwable $throwable
     * @return boolean
     */
    public function isValid(Throwable $throwable): bool
    {
        return true;
    }

    /**
     * Convert the given exception to an array.
     *
     * @param  \Throwable  $e
     * @return array
     */
    protected function convertExceptionToArray(Throwable $e)
    {
        return config('debug', false) ? [
            'error'     => 3000,
            'message'   => $e->getMessage(),
            'exception' => get_class($e),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => explode("\n", $e->getTraceAsString())
        ] : [
            'error'     => 3000,
            'message'   => $this->isHttpException($e) ? $e->getMessage() : 'Server Error'
        ];
    }

    protected function isHttpException(Throwable $e)
    {
        return $e instanceof NotFoundHttpException || $e instanceof MethodNotAllowedHttpException;
    }

    private function sendError($error)
    {
        $span = $this->startSpan('error');
        $span->setTag('coroutine.id', (string) Coroutine::id());
        $span->setTag('error', \json_encode($error, JSON_PRETTY_PRINT));
        $span->finish();

        defer(function () {
            $this->tracer->flush();
        });
    }
}