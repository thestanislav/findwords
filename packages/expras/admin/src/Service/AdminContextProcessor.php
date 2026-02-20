<?php

namespace ExprAs\Admin\Service;

use Monolog\LogRecord;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Admin Context Processor
 * 
 * Automatically adds admin-specific context to log records:
 * - user, resource, action, httpMethod, requestUri, requestData, ipAddress
 * 
 * This processor should be attached to the admin.api.logger
 */
class AdminContextProcessor
{
    public function __construct(private \Closure $requestFactory)
    {
    }

    /**
     * Adds admin context to the log record
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $request = call_user_func($this->requestFactory);
        
        if (!$request instanceof ServerRequestInterface) {
            return $record;
        }

        $context = $record->context;

        try {
            // Add authenticated user
            $user = $request->getAttribute(UserInterface::class);
            if ($user) {
                $context['user'] = $user;
            }

            // Add resource name from route attribute
            $resource = $request->getAttribute('resource');
            if ($resource) {
                $context['resource'] = $resource;
            }

            // Add action from route attribute
            $action = $request->getAttribute('action');
            if ($action) {
                $context['action'] = $action;
            }

            // Add HTTP method
            $context['httpMethod'] = $request->getMethod();

            // Add request URI
            $context['requestUri'] = (string) $request->getUri();

            // Add request data (body)
            $requestData = $request->getParsedBody();
            if ($requestData) {
                $context['requestData'] = $requestData;
            }

            // Add entity ID if present in route
            $entityId = $request->getAttribute('id');
            if ($entityId) {
                $context['entityId'] = (string) $entityId;
            }

            // Add IP address
            $serverParams = $request->getServerParams();
            $context['ipAddress'] = $serverParams['HTTP_CLIENT_IP'] 
                ?? $serverParams['HTTP_X_FORWARDED_FOR'] 
                ?? $serverParams['REMOTE_ADDR'] 
                ?? null;

        } catch (\Throwable $e) {
            // Silently fail - don't break logging if context extraction fails
        }

        return $record->with(context: $context);
    }
}

