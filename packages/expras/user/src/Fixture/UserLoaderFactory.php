<?php

namespace ExprAs\User\Fixture;

use Psr\Container\ContainerInterface;

class UserLoaderFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new UserLoader($container);
    }
}