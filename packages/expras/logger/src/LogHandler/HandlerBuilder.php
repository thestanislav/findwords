<?php

namespace ExprAs\Logger\LogHandler;

use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Level;
use Psr\Container\ContainerInterface;
use Doctrine\ORM\EntityManager;
use ExprAs\Logger\Entity\ErrorLogEntity;

/**
 * Handler Builder
 * 
 * Factory for creating Monolog handlers from configuration.
 * Implements Factory Method pattern for handler creation.
 */
class HandlerBuilder
{
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * Build a handler from configuration
     * 
     * @param string $name Handler type name (stream, file, doctrine, syslog, custom)
     * @param array $config Handler configuration
     * @return HandlerInterface|null
     */
    public function build(string $name, array $config): ?HandlerInterface
    {
        return match ($name) {
            'stream' => $this->buildStreamHandler($config),
            'file', 'rotating_file' => $this->buildRotatingFileHandler($config),
            'doctrine' => $this->buildDoctrineHandler($config),
            'syslog' => $this->buildSyslogHandler($config),
            'custom' => $this->buildCustomHandler($config),
            default => null,
        };
    }

    /**
     * Build a stream handler (simple file or stdout/stderr)
     */
    private function buildStreamHandler(array $config): StreamHandler
    {
        $options = $config['options'] ?? [];

        return new StreamHandler(
            $options['stream'] ?? 'php://stderr',
            $this->parseLevel($options['level'] ?? Level::Debug),
            $options['bubble'] ?? true,
            $options['filePermission'] ?? null,
            $options['useLocking'] ?? false
        );
    }

    /**
     * Build a rotating file handler (daily/weekly rotation)
     */
    private function buildRotatingFileHandler(array $config): RotatingFileHandler
    {
        $options = $config['options'] ?? [];

        return new RotatingFileHandler(
            $options['path'] ?? 'data/logs/app.log',
            $options['maxFiles'] ?? 30,
            $this->parseLevel($options['level'] ?? Level::Debug),
            $options['bubble'] ?? true,
            $options['filePermission'] ?? null,
            $options['useLocking'] ?? false
        );
    }

    /**
     * Build a doctrine handler (database logging)
     */
    private function buildDoctrineHandler(array $config): DoctrineHandler
    {
        $options = $config['options'] ?? [];

        $entityManager = $this->container->get(EntityManager::class);
        
        return new DoctrineHandler(
            $entityManager,
            null, // hydrator (will use default)
            $options['entity'] ?? ErrorLogEntity::class,
            $this->parseLevel($options['level'] ?? Level::Debug),
            $options['bubble'] ?? true
        );
    }

    /**
     * Build a syslog handler
     */
    private function buildSyslogHandler(array $config): SyslogHandler
    {
        $options = $config['options'] ?? [];

        return new SyslogHandler(
            $options['ident'] ?? 'expras',
            $options['facility'] ?? LOG_USER,
            $this->parseLevel($options['level'] ?? Level::Debug),
            $options['bubble'] ?? true,
            $options['logopts'] ?? LOG_PID
        );
    }

    /**
     * Build a custom handler from service name
     *
     * For factory services that need options (like ErrorDoctrineHandlerFactory),
     * this method calls the factory directly with options.
     */
    private function buildCustomHandler(array $config): ?HandlerInterface
    {
        $serviceName = $config['options']['service'] ?? null;
        if (!$serviceName) {
            return null;
        }

        $options = $config['options'] ?? [];

        // Check if the service definition is a factory (not an already instantiated service)
        $serviceDefinitions = $this->container->get('config')['dependencies']['factories'] ?? [];
        if (isset($serviceDefinitions[$serviceName])) {
            // It's a factory, instantiate it and call with options
            $factoryClass = $serviceDefinitions[$serviceName];
            if (class_exists($factoryClass)) {
                $factory = new $factoryClass();
                if (is_callable($factory)) {
                    $handler = $factory($this->container, $serviceName, $options);
                    if ($handler instanceof HandlerInterface) {
                        return $handler;
                    }
                }
            }
        } else {
            // It's not a factory, get the service directly
            $service = $this->container->get($serviceName);
            if ($service instanceof HandlerInterface) {
                return $service;
            }
        }

        return null;
    }

    /**
     * Parse level from config (supports Level enum, string, or int)
     */
    private function parseLevel(mixed $level): Level
    {
        if ($level instanceof Level) {
            return $level;
        }

        if (is_string($level)) {
            return Level::fromName(ucfirst(strtolower($level)));
        }

        if (is_int($level)) {
            return Level::from($level);
        }

        return Level::Debug;
    }
}

