<?php

namespace ExprAs\Logger\Service;

use Psr\Log\LoggerInterface;

/**
 * Logger Registry Service
 * 
 * Central registry for all loggers created in the application.
 * Enables discovery and management of all loggers across all modules.
 */
class LoggerRegistry
{
    /**
     * @var array<string, LoggerInterface>
     */
    private array $loggers = [];

    /**
     * @var array<string, array>
     */
    private array $metadata = [];

    /**
     * Register a logger with optional metadata
     * 
     * @param string $name Logger name (e.g., 'expras_logger', 'admin.api.logger')
     * @param LoggerInterface $logger The logger instance
     * @param array $metadata Optional metadata (entity, description, module, etc.)
     */
    public function register(string $name, LoggerInterface $logger, array $metadata = []): void
    {
        $this->loggers[$name] = $logger;
        $this->metadata[$name] = $metadata;
    }

    /**
     * Get a logger by name
     * 
     * @param string $name Logger name
     * @return LoggerInterface|null
     */
    public function getLogger(string $name): ?LoggerInterface
    {
        return $this->loggers[$name] ?? null;
    }

    /**
     * Get all registered loggers
     * 
     * @return array<string, LoggerInterface>
     */
    public function getAllLoggers(): array
    {
        return $this->loggers;
    }

    /**
     * Get metadata for a specific logger
     * 
     * @param string $name Logger name
     * @return array|null
     */
    public function getLoggerMetadata(string $name): ?array
    {
        return $this->metadata[$name] ?? null;
    }

    /**
     * Get all metadata for all loggers
     * 
     * @return array<string, array>
     */
    public function getAllMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Check if a logger is registered
     * 
     * @param string $name Logger name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->loggers[$name]);
    }

    /**
     * Get count of registered loggers
     * 
     * @return int
     */
    public function count(): int
    {
        return count($this->loggers);
    }
}

