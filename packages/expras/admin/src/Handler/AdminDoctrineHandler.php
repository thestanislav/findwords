<?php

namespace ExprAs\Admin\Handler;

use ExprAs\Logger\LogHandler\DoctrineHandler;
use ExprAs\User\Entity\UserSuper;
use Monolog\LogRecord;

/**
 * Admin-specific Doctrine Handler
 * 
 * Extends the base DoctrineHandler to add admin-specific field mapping
 * for AdminRequestLogEntity (user, resource, action, request data, etc.).
 */
class AdminDoctrineHandler extends DoctrineHandler
{
    /**
     * Map additional admin-specific context fields to entity
     * 
     * @param object $entity AdminRequestLogEntity instance
     * @param LogRecord $record Log record with context
     */
    protected function mapAdditionalFields(object $entity, LogRecord $record): void
    {
        // Map admin-specific scalar fields from context to entity
        $this->setOptionalField($entity, 'setResource', $record->context['resource'] ?? null);
        $this->setOptionalField($entity, 'setAction', $record->context['action'] ?? null);
        $this->setOptionalField($entity, 'setHttpMethod', $record->context['httpMethod'] ?? null);
        $this->setOptionalField($entity, 'setRequestUri', $record->context['requestUri'] ?? null);
        $this->setOptionalField($entity, 'setRequestData', $record->context['requestData'] ?? null);
        $this->setOptionalField($entity, 'setResponseStatus', $record->context['responseStatus'] ?? null);
        $this->setOptionalField($entity, 'setEntityId', $record->context['entityId'] ?? null);
        $this->setOptionalField($entity, 'setIpAddress', $record->context['ipAddress'] ?? null);
        
        // Set user entity reference if available from request attribute
        if (isset($record->context['user']) && $record->context['user'] instanceof UserSuper) {
            $this->setOptionalField($entity, 'setUser', $record->context['user']);
        }
    }
}

