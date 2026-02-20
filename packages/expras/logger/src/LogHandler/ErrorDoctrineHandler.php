<?php

namespace ExprAs\Logger\LogHandler;

use Monolog\LogRecord;

/**
 * Error-specific Doctrine Handler
 * 
 * Extends the base DoctrineHandler to add error-specific field mapping
 * for ErrorLogEntity (file, line, request data, IP address).
 */
class ErrorDoctrineHandler extends DoctrineHandler
{
    /**
     * Map error-specific fields from record to entity
     *
     * File and line information comes from IntrospectionProcessor (in extra).
     * Request information comes from RequestDataProcessor (in context).
     *
     * @param object $entity ErrorLogEntity instance
     * @param LogRecord $record Log record with context and extra data
     */
    protected function mapAdditionalFields(object $entity, LogRecord $record): void
    {
        // File and line from IntrospectionProcessor (extra) or manual context
        $this->setOptionalField($entity, 'setFile', $record->extra['file'] ?? $record->context['file'] ?? null);
        $this->setOptionalField($entity, 'setLine', $record->extra['line'] ?? $record->context['line'] ?? null);

        // Request data from RequestDataProcessor (context)
        $this->setOptionalField($entity, 'setRequestUri', $record->context['requestUri'] ?? null);
        $this->setOptionalField($entity, 'setRequestMethod', $record->context['requestMethod'] ?? null);
        $this->setOptionalField($entity, 'setRequestBody', $record->context['requestBody'] ?? null);
        $this->setOptionalField($entity, 'setIpAddress', $record->context['ipAddress'] ?? null);
    }
}

