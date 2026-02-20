<?php

declare(strict_types=1);

namespace App;

use App\Console\CrawlErrors;
use App\Console\PhonemeRebuild;
use App\Handler\FiveLettersHandler;
use App\Middleware\NavigationStateResetMiddleware;
use App\Middleware\NavigationStateResetMiddlewareFactory;
use App\Middleware\PageCacheMiddleware;
use App\Middleware\PageCacheMiddlewareFactory;
use App\Middleware\PluralRuleSetter;
use App\Navigation\DictionaryNavigationFactory;
use App\PageCache\StorageAdapterFactory;
use App\Service\SphinxQLConnectionFactory;
use ExprAs\Core\ConfigAggregator\InvokableProvider;
use ExprAs\Core\ModuleConfigProvider\AbstractProvider;
use ExprAs\Logger\Service\LoggingErrorListenerDelegator;
use Laminas\Stratigility\Middleware\ErrorHandler;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
class ConfigProvider extends AbstractProvider
{

    /**
     * Returns the container dependencies
     */
    public function getDependencies(): array
    {
        return [
            'invokables' => [
                Handler\PingHandler::class,
                Handler\HomePageHandler::class,
                Handler\RhymePageHandler::class,
                Handler\AnagramPageHandler::class,
                Handler\WordPageHandler::class,
                Handler\CrossWordPageHandler::class,
                PluralRuleSetter::class,
                PhonemeRebuild::class,
                CrawlErrors::class,
                FiveLettersHandler::class
            ],
            'factories'  => [
                NavigationStateResetMiddleware::class => NavigationStateResetMiddlewareFactory::class,
                PageCacheMiddleware::class => PageCacheMiddlewareFactory::class,
                'DictionaryNavigation' => DictionaryNavigationFactory::class,
                \Foolz\SphinxQL\Drivers\Mysqli\Connection::class => SphinxQLConnectionFactory::class,
                'page_cache_storage_adapter' => StorageAdapterFactory::class
            ],
            'delegators'         => [
                ErrorHandler::class => [
                    LoggingErrorListenerDelegator::class,
                ],
            ],
        ];
    }

    public function getDependantModules(): array
    {
        return [
            new InvokableProvider(\ExprAs\Core\ConfigProvider::class),
            new InvokableProvider(\ExprAs\Doctrine\ConfigProvider::class),
            new InvokableProvider(\ExprAs\Asset\ConfigProvider::class),
            new InvokableProvider(\ExprAs\Logger\ConfigProvider::class),
            new InvokableProvider(\ExprAs\Rest\ConfigProvider::class),
            new InvokableProvider(\ExprAs\View\ConfigProvider::class),
            //new InvokableProvider(\ExprAs\Admin\ConfigProvider::class),
        ];
    }

}
