<?php

namespace ExprAs\Admin\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Mezzio\Authentication\UserInterface;

/**
 * Admin API Logging Middleware
 * 
 * Automatically logs all admin REST API requests.
 * Logs successful operations at INFO level, errors at ERROR level.
 */
class AdminApiLoggingMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Get request details before processing
        $resource = $request->getAttribute('resource');
        $action = $request->getAttribute('action');
        $method = $request->getMethod();
        
        // Process the request
        $startTime = microtime(true);
        
        try {
            $response = $handler->handle($request);
            $duration = microtime(true) - $startTime;
            
            // Log successful request
            $this->logRequest($request, $response, $duration, null);
            
            return $response;
            
        } catch (\Throwable $e) {
            $duration = microtime(true) - $startTime;
            
            // Log failed request
            $this->logRequest($request, null, $duration, $e);
            
            throw $e;  // Re-throw the exception
        }
    }

    /**
     * Log the API request
     */
    private function logRequest(
        ServerRequestInterface $request,
        ?ResponseInterface $response,
        float $duration,
        ?\Throwable $error
    ): void {
        $resource = $request->getAttribute('resource');
        $action = $request->getAttribute('action');
        
        // Skip logging for non-API requests (no resource/action)
        if (!$resource || !$action) {
            return;
        }

        $statusCode = $response?->getStatusCode();
        $level = $error ? 'error' : 'info';
        
        // Build log message
        $user = $request->getAttribute(UserInterface::class);
        $username = $user ? ($user->getDetail('username') ?? $user->getIdentity()) : 'anonymous';
        
        $message = sprintf(
            '%s %s.%s [%s] - %dms',
            $request->getMethod(),
            $resource,
            $action,
            $statusCode ?? ($error ? 'ERROR' : 'N/A'),
            (int)($duration * 1000)
        );

        // Build comprehensive context with all admin-specific fields
        $serverParams = $request->getServerParams();
        
        $context = [
            // Admin-specific fields
            'user' => $user,  // UserSuper entity
            'resource' => $resource,
            'action' => $action,
            'httpMethod' => $request->getMethod(),
            'requestUri' => (string) $request->getUri(),
            'requestData' => $request->getParsedBody(),
            'entityId' => $request->getAttribute('id'),
            'ipAddress' => $serverParams['HTTP_CLIENT_IP'] 
                ?? $serverParams['HTTP_X_FORWARDED_FOR'] 
                ?? $serverParams['REMOTE_ADDR'] 
                ?? null,
            
            // Performance
            'duration' => $duration,
        ];
        
        // Add response status if available
        if ($statusCode) {
            $context['responseStatus'] = $statusCode;
        }
        
        // Add error details if failed
        if ($error) {
            $context['error'] = $error->getMessage();
            $context['errorFile'] = $error->getFile();
            $context['errorLine'] = $error->getLine();
        }

        $this->logger->log($level, $message, $context);
    }
}

