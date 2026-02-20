<?php

namespace ExprAs\Admin\Handler;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

class JsonServerRestApiFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param string            $requestedName
     * @param null|array<mixed> $options
     *
     * @return object
     * @throws ServiceNotFoundException If unable to resolve the service.
     * @throws ServiceNotCreatedException If an exception is raised when creating a service.
     * @throws ContainerExceptionInterface If any other error occurs.
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        if (class_exists($requestedName) && is_subclass_of($requestedName, JsonServerRestApiHandler::class)) {
            return new $requestedName;
        }
        return new JsonServerRestApiHandler();
    }
}
