<?php

namespace ExprAs\Nutgram\Service;

use Monolog\LogRecord;
use SergiX44\Nutgram\Nutgram;

/**
 * Telegram Context Processor
 * 
 * Automatically adds Telegram-specific context to log records:
 * - updateId, chatId, userId, handler, messageText, updateType
 * 
 * This processor should be attached to the nutgram.logger
 */
class TelegramContextProcessor
{
    public function __construct(private Nutgram $bot)
    {
    }

    /**
     * Adds Telegram context to the log record
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $context = $record->context;

        try {
            // Add update ID if available
            if ($this->bot->update()?->update_id) {
                $context['updateId'] = $this->bot->update()->update_id;
            }

            // Add chat entity (already fetched by ChatEntityInjectorMiddleware)
            if ($chatEntity = $this->bot->get('expras.nutgram.chat')) {
                $context['chat'] = $chatEntity;
            }

            // Add user entity (already fetched by UserEntityInjectorMiddleware)
            if ($userEntity = $this->bot->get('expras.nutgram.user')) {
                $context['user'] = $userEntity;
            }

            // Add update type (message, callback_query, etc.)
            if ($this->bot->update()) {
                $context['update'] = $this->bot->update()->toArray();
                $updateType = $this->bot->update()->getType();
                if ($updateType) {
                    // Convert UpdateType enum to string
                    $context['updateType'] = $updateType->value ?? (string) $updateType;
                }
            }

            // Add handler info (auto-detect or use manually provided)
            if (isset($record->context['handler'])) {
                // Use manually provided handler class name
                $context['handler'] = $record->context['handler'];
            } else {
                // Try to auto-detect current handler from Nutgram
                $currentHandler = $this->bot->currentHandler();
                if ($currentHandler !== null) {
                    $context['handler'] = is_object($currentHandler) 
                        ? get_class($currentHandler) 
                        : (is_string($currentHandler) ? $currentHandler : null);
                }
            }

        } catch (\Throwable $e) {
            // Silently fail - don't break logging if Telegram context not available
        }

        return $record->with(context: $context);
    }
}

