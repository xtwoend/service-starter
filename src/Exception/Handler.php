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
        $statusCode = $this->isHttpException($throwable)? $throwable->getStatusCode(): 500;
        $errors = $this->convertExceptionToArray($throwable);

        if(config('debug')){
            $this->logger->error(
                sprintf(
                    '%s[%s] in %s',
                    $throwable->getMessage(),
                    $throwable->getLine(),
                    $throwable->getFile()
                ));

            $this->logger->error($throwable->getTraceAsString());
            $this->sendError($errors);
        }

        if(config('app_env') === 'prod')
        {
            $errors = [
                'error'     => 3000,
                'message'   => $this->isHttpException($throwable) ? $throwable->getMessage() : 'Server Error'
            ];
        }

        return $response
            ->withHeader('Server', 'Hyperf')
            ->withStatus($statusCode)
            ->withAddedHeader('content-type', 'application/json')
            ->withBody(new SwooleStream(Json::encode($errors)));
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
        return [
            'error'     => 3000,
            'message'   => $e->getMessage(),
            'exception' => get_class($e),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => explode("\n", $e->getTraceAsString())
        ];
    }

    protected function isHttpException(Throwable $e)
    {
        return $e instanceof NotFoundHttpException || $e instanceof MethodNotAllowedHttpException;
    }

    // tracer for app debug
    protected function sendError($errors)
    {
        $span = $this->startSpan('error');
        $span->setTag('coroutine.id', (string) Coroutine::id());
        $span->setTag('error', \json_encode($errors, JSON_PRETTY_PRINT));
        $span->finish();
    }
}