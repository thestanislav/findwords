<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/9/2017
 * Time: 00:00
 */

namespace ExprAs\User;

use ExprAs\User\Handler\UserRestApiHandler;
use ExprAs\User\MezzioAuthentication\AdapterChain;
use ExprAs\User\MezzioAuthentication\AdapterChainFactory;
use ExprAs\Core\ConfigAggregator\InvokableProvider;
use ExprAs\Core\ModuleConfigProvider\AbstractProvider;
use ExprAs\User\MezzioAuthentication\DoctrineAdapter;
use ExprAs\User\MezzioAuthentication\DoctrineAdapterFactory;
use ExprAs\User\MezzioAuthentication\SessionAdapter;
use ExprAs\User\MezzioAuthentication\SessionAdapterFactory;
use ExprAs\User\MezzioAuthentication\SessionContainer;
use ExprAs\User\Middleware\MezzioAuthenticationMiddleware;
use ExprAs\User\Middleware\MezzioAuthenticationMiddlewareFactory;
use ExprAs\User\Middleware\UserActivityTrackerMiddleware;
use ExprAs\User\Middleware\UserActivityTrackerMiddlewareFactory;
use ExprAs\User\OwnerAware\OwnerListener;
use ExprAs\User\OwnerAware\OwnerListenerInjectorMiddleware;
use ExprAs\User\Service\RememberMeService;
use ExprAs\User\Service\RememberMeServiceFactory;
use ExprAs\User\DoctrineListener\RememberMeUserModifierListener;
use ExprAs\User\DoctrineListener\RememberMeUserModifierListenerFactory;
use ExprAs\User\View\Helper\Identity;
use ExprAs\User\MezzioAuthentication\RememberMeAdapterFactory;
use ExprAs\User\MezzioAuthentication\RememberMeAdapter;
use ExprAs\User\View\Helper\IdentityFactoryMiddleware;
use ExprAs\User\View\Helper\IdentityFactoryMiddlewareFactory;
use ExprAs\User\Fixture\UserLoader;
use ExprAs\User\Fixture\UserLoaderFactory;
use Mezzio\Authentication\AuthenticationInterface;

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
            'factories' => [

                RememberMeService::class => RememberMeServiceFactory::class,
                RememberMeUserModifierListener::class => RememberMeUserModifierListenerFactory::class,

                DoctrineAdapter::class => DoctrineAdapterFactory::class,
                RememberMeAdapter::class => RememberMeAdapterFactory::class,
                SessionAdapter::class => SessionAdapterFactory::class,
                AdapterChain::class => AdapterChainFactory::class,

                MezzioAuthenticationMiddleware::class => MezzioAuthenticationMiddlewareFactory::class,
                UserActivityTrackerMiddleware::class => UserActivityTrackerMiddlewareFactory::class,
                UserLoader::class =>    UserLoaderFactory::class,
                IdentityFactoryMiddleware::class => IdentityFactoryMiddlewareFactory::class,
            ],

            'invokables' => [
                UserRestApiHandler::class,
                OwnerListener::class,
                OwnerListenerInjectorMiddleware::class,
                SessionContainer::class,
            ],
            'aliases' => [
                AuthenticationInterface::class =>  AdapterChain::class,
            ],
            'shared' => [
                UserRestApiHandler::class => false,
            ],
        ];

    }

    #[\Override]
    public function getDependantModules()
    {
        return [
            new InvokableProvider(\ExprAs\Core\ConfigProvider::class),
            new InvokableProvider(\ExprAs\Rbac\ConfigProvider::class),
            new InvokableProvider(\Mezzio\Authentication\ConfigProvider::class),
            new InvokableProvider(\Laminas\Session\ConfigProvider::class),
        ];
    }

    #[\Override]
    public function getConfig()
    {
        return [
            'view_helpers' => [
                'aliases' => [
                    Identity::class => 'identity',
                    'Identity' => 'identity',
                ]
            ]
        ];
    }
}
