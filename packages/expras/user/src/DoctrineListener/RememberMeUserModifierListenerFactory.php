<?php

namespace ExprAs\User\DoctrineListener;

use Psr\Container\ContainerInterface;

class RememberMeUserModifierListenerFactory
{
    public function __invoke(ContainerInterface $container): RememberMeUserModifierListener
    {
        return new RememberMeUserModifierListener($container);
    }
}

