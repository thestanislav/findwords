<?php

namespace ExprAs\Logger\Api;

use ExprAs\Logger\Service\LoggerRegistry;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Logger List Handler
 * 
 * REST API endpoint that returns a list of all registered loggers.
 * Useful for admin interfaces to discover and manage loggers.
 * 
 * GET /api/admin/loggers
 */
class LoggerListHandler implements RequestHandlerInterface
{
    public function __construct(private LoggerRegistry $registry)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $loggers = [];
        
        foreach ($this->registry->getAllMetadata() as $name => $metadata) {
            $loggers[] = [
                'name' => $name,
                'entity' => $metadata['entity'] ?? null,
                'description' => $metadata['description'] ?? '',
                'module' => $metadata['module'] ?? '',
                'channel' => $metadata['name'] ?? $name,
            ];
        }

        return new JsonResponse([
            'success' => true,
            'data' => $loggers,
            'count' => count($loggers),
        ]);
    }
}

