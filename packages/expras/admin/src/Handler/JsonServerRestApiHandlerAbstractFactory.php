<?php

namespace ExprAs\Admin\Handler;

use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Abstract factory for per-entity REST API handlers.
 *
 * Creates a new JsonServerRestApiHandler for each requested service name
 * matching exprass_admin.rest_handler.{resourceName}. Used so that under
 * mezzio-swoole each request gets a fresh handler instance (when combined
 * with shared => false per service name), avoiding cross-request state and
 * hydrator mutation.
 */
class JsonServerRestApiHandlerAbstractFactory implements AbstractFactoryInterface
{
    public const SERVICE_PREFIX = 'exprass_admin.rest_handler.';

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName): bool
    {
        if (!is_string($requestedName)) {
            return false;
        }
        if (!str_starts_with($requestedName, self::SERVICE_PREFIX)) {
            return false;
        }
        $suffix = substr($requestedName, strlen(self::SERVICE_PREFIX));

        return $suffix !== '';
    }

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return JsonServerRestApiHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): JsonServerRestApiHandler
    {
        return new JsonServerRestApiHandler();
    }
}
