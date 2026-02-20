<?php

namespace ExprAs\User\MezzioAuthentication;

use ExprAs\User\Service\RememberMeService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class RememberMeAdapterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {

        return new RememberMeAdapter($container->get(ResponseInterface::class), $container->get(RememberMeService::class));
    }
}
