<?php

namespace ExprAs\User\Middleware;

use Mezzio\Authentication;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class MezzioAuthenticationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): MezzioAuthenticationMiddleware
    {
        $authentication = $container->has(Authentication\AuthenticationInterface::class)
            ? $container->get(Authentication\AuthenticationInterface::class)
                : null;


        if (null === $authentication) {
            throw new Authentication\Exception\InvalidConfigException(
                'AuthenticationInterface service is missing'
            );
        }

        return new MezzioAuthenticationMiddleware($authentication);
    }
}
