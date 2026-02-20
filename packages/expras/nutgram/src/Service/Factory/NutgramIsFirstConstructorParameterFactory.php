<?php

namespace ExprAs\Nutgram\Service\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use SergiX44\Nutgram\Nutgram;

class NutgramIsFirstConstructorParameterFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new $requestedName($container->get(Nutgram::class));
    }
}