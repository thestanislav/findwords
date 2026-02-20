<?php

namespace ExprAs\Doctrine\Container;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

class DoctrineCommandsDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        $application = $callback();
        ConsoleRunner::addCommands($application, new SingleManagerProvider($container->get(EntityManager::class)));

        return $application;
    }
}
