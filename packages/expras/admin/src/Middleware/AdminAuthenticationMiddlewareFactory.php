<?php

namespace ExprAs\Admin\Middleware;

use Mezzio\Authentication;
use Psr\Container\ContainerInterface;

class AdminAuthenticationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): AdminAuthenticationMiddleware
    {
        $authentication = $container->has(Authentication\AuthenticationInterface::class)
            ? $container->get(Authentication\AuthenticationInterface::class)
                : null;


        if (null === $authentication) {
            throw new Authentication\Exception\InvalidConfigException(
                'AuthenticationInterface service is missing'
            );
        }

        return new AdminAuthenticationMiddleware($authentication);
    }
}
