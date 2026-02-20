<?php

namespace ExprAs\Logger\Processor;

use Monolog\LogRecord;
use Psr\Http\Message\ServerRequestInterface;

class RequestDataProcessor
{
    public function __construct(protected \Closure $requestFactory)
    {
    }

    /**
     * Adds request data to the log record context
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $request = call_user_func($this->requestFactory);
        
        if ($request instanceof ServerRequestInterface) {
            $serverParams = $request->getServerParams();
            
            $context = $record->context;
            $context['requestMethod'] = $request->getMethod();
            $context['requestUri'] = (string) $request->getUri();
            $context['requestBody'] = $request->getParsedBody();
            $context['ipAddress'] = $serverParams['HTTP_CLIENT_IP'] ?? 
                                   $serverParams['HTTP_X_FORWARDED_FOR'] ?? 
                                   $serverParams['REMOTE_ADDR'] ?? null;
            
            return $record->with(context: $context);
        }
        
        return $record;
    }
}
