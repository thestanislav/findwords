<?php

namespace ExprAs\Core\PhpSettings;

use Mezzio\Application as MezzioApplication;
use Symfony\Component\Console\Application as ConsoleApplication;
use Psr\Container\ContainerInterface;

class ConfigSetterDelegator
{
    /**
     * @param  $serviceName
     * @return Application
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): MezzioApplication | ConsoleApplication
    {
        $config = $container->get('config');
        if (isset($config['php_settings'])) {
            $container->get(SettingManager::class)->configSet($config['php_settings']);
        }

        return $callback();
    }
}
