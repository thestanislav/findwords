<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/9/2017
 * Time: 00:00
 */

namespace ExprAs\Rbac;

use ExprAs\Core\ConfigAggregator\InvokableProvider;
use ExprAs\Core\ModuleConfigProvider\AbstractProvider;
use ExprAs\Core\ServiceManager\Factory\ContainerInvokableFactory;
use ExprAs\Rbac\Console\RoleAdd;
use ExprAs\Telegram\Container\BotsManagerFactory;
use ExprAs\Telegram\Container\BotsManager;
use ExprAs\Telegram\HttpClient\ProxibleGuzzleHttpClient;
use ExprAs\Telegram\HttpClient\ProxibleGuzzleHttpClientFactory;
use ExprAs\Telegram\Middleware\ContactListenerMiddleware;
use ExprAs\Telegram\Middleware\ContainerInjectionListener;

class ConfigProvider extends AbstractProvider
{
    /**
     * Returns the container dependencies
     *
     * @return array
     */
    #[\Override]
    public function getDependencies()
    {
        return [
            'invokables' => [
                RoleAdd::class
            ],
            'factories' => [

            ],
            'aliases' => [

            ]
        ];
    }

    #[\Override]
    public function getDependantModules()
    {
        return [
            new InvokableProvider(\ExprAs\Core\ConfigProvider::class)
        ];
    }
}
