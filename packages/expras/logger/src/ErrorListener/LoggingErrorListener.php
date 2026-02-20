<?php

namespace ExprAs\Logger\ErrorListener;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use ErrorException;
use Monolog\Level;

class LoggingErrorListener
{
    protected $_logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->_logger = $logger;
    }

    public function __invoke(
        Throwable $exception,
        ServerRequestInterface $request,
        ResponseInterface $response
    ) {

        $logMessages = [];

        do {
            $level = Level::Error;
            if ($exception instanceof ErrorException) {
                $level = $this->mapErrorSeverityToMonologLevel($exception->getSeverity());
            }

            $context = [
                'file'  => $exception->getFile(),
                'line'  => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ];
            if (isset($exception->xdebug_message)) {
                $context['xdebug'] = $exception->xdebug_message;
            }

            $logMessages[] = [
                'level' => $level,
                'message'  => $exception->getMessage(),
                'context'    => $context,
            ];
            $exception = $exception->getPrevious();

        } while ($exception);

        foreach (array_reverse($logMessages) as $logMessage) {
            $this->_logger->log($logMessage['level'], $logMessage['message'], $logMessage['context']);
        }
    }
    
    private function mapErrorSeverityToMonologLevel(int $severity): Level
    {
        return match($severity) {
            E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR => Level::Error,
            E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING => Level::Warning,
            E_NOTICE, E_USER_NOTICE => Level::Notice,
            E_RECOVERABLE_ERROR => Level::Error,
            E_DEPRECATED, E_USER_DEPRECATED => Level::Warning,
            default => Level::Error,
        };
    }
}
