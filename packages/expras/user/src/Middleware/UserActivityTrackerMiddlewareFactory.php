<?php

namespace ExprAs\User\Middleware;

use Doctrine\ORM\EntityManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class UserActivityTrackerMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        /**
         * @var EntityManager $em
         */
        $em = $container->get(EntityManager::class);
        return new UserActivityTrackerMiddleware($em);
    }
}

