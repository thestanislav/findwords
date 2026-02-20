<?php

namespace ExprAs\Core\ServiceManager\Delegator;
use Mezzio\Application;
use Psr\Container\ContainerInterface;
class EnvironmentVariablesInjector
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        $app = $callback();
        $env = $container->get('config')['env']??[];

        foreach ($env as $key => $value) {
            putenv($key . '=' . $value);
        }

        return $app;
    }
}