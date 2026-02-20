<?php

namespace ExprAs\Logger\Service;

use ExprAs\Logger\Processor\RequestDataProcessor;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestDataProcessorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): RequestDataProcessor
    {
        $requestFactory = $options['requestFactory'] ??
            ($container->has(ServerRequestInterface::class) ? $container->get(ServerRequestInterface::class) : fn() => null);

        return new RequestDataProcessor($requestFactory);
    }
}
