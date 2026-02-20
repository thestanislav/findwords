<?php

declare(strict_types=1);

namespace ExprAs\User\View\Helper;

use ExprAs\Core\Http\CurrentRequestHolder;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class IdentityFactoryMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): IdentityFactoryMiddleware
    {
        return new IdentityFactoryMiddleware(
            $container->get(CurrentRequestHolder::class)
        );
    }
}
