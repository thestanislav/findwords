<?php

declare(strict_types=1);

namespace ExprAs\Core\ErrorListener;

use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

class SwooleErrorListener
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function __invoke(
        Throwable $exception,
        ServerRequestInterface $request,
        ResponseInterface $response
    ): void {
        $messages = [];

        do {
            $level = $exception instanceof ErrorException
                ? $this->mapSeverityToLogLevel($exception->getSeverity())
                : LogLevel::ERROR;

            $messages[] = [
                'level'   => $level,
                'message' => sprintf('[%s] %s', $exception::class, $exception->getMessage()),
                'context' => [
                    'file'  => $exception->getFile(),
                    'line'  => $exception->getLine(),
                    'trace' => $exception->getTraceAsString(),
                ],
            ];
            $exception = $exception->getPrevious();
        } while ($exception);

        foreach (array_reverse($messages) as $msg) {
            $this->logger->log($msg['level'], $msg['message'], $msg['context']);
        }
    }

    private function mapSeverityToLogLevel(int $severity): string
    {
        return match ($severity) {
            E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR => LogLevel::ERROR,
            E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING => LogLevel::WARNING,
            E_NOTICE, E_USER_NOTICE => LogLevel::NOTICE,
            E_RECOVERABLE_ERROR => LogLevel::ERROR,
            E_DEPRECATED, E_USER_DEPRECATED => LogLevel::WARNING,
            default => LogLevel::ERROR,
        };
    }
}
