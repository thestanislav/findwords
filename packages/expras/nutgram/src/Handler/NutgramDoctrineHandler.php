<?php

namespace ExprAs\Nutgram\Handler;

use ExprAs\Logger\LogHandler\DoctrineHandler;
use ExprAs\Nutgram\Entity\Chat;
use ExprAs\Nutgram\Entity\User;
use Monolog\LogRecord;

/**
 * Nutgram-specific Doctrine Handler
 * 
 * Extends the base DoctrineHandler to add Telegram-specific field mapping.
 * This keeps the core DoctrineHandler generic while allowing modules
 * to add their own field mappings.
 */
class NutgramDoctrineHandler extends DoctrineHandler
{
    /**
     * Map additional Telegram-specific context fields to entity
     * 
     * @param object $entity TelegramLogEntity instance
     * @param LogRecord $record Log record with context
     */
    protected function mapAdditionalFields(object $entity, LogRecord $record): void
    {
        // Map Telegram-specific fields from context to entity
        $this->setOptionalField($entity, 'setUpdateId', $record->context['updateId'] ?? null);
        $this->setOptionalField($entity, 'setHandler', $record->context['handler'] ?? null);
        $this->setOptionalField($entity, 'setMessageText', $record->context['messageText'] ?? null);
        $this->setOptionalField($entity, 'setUpdateType', $record->context['updateType'] ?? null);
        $this->setOptionalField($entity, 'setUpdate', $record->context['update'] ?? null);
        // Set entity references directly from context
        // These entities are already fetched and stored by UserEntityInjectorMiddleware
        // and ChatEntityInjectorMiddleware, so we just reuse them - no extra DB queries!
        if (isset($record->context['chat']) && $record->context['chat'] instanceof Chat) {
            $this->setOptionalField($entity, 'setChat', $record->context['chat']);
        }
        
        if (isset($record->context['user']) && $record->context['user'] instanceof User) {
            $this->setOptionalField($entity, 'setUser', $record->context['user']);
        }
    }
}

