<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/9/2017
 * Time: 00:00
 */

namespace ExprAs\Uploadable;

use ExprAs\Core\ConfigAggregator\InvokableProvider;
use ExprAs\Core\ModuleConfigProvider\AbstractProvider;
use ExprAs\Uploadable\EventListener\UploadableListener;
use ExprAs\Uploadable\EventListener\UploadableListenerFactory;
use ExprAs\Uploadable\Handler\FetchHandler;
use ExprAs\Uploadable\Middleware\UploadableEntityInjectionMiddleware;

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
                UploadableListener::class => UploadableListenerFactory::class
            ],

            'invokables' => [
                UploadableEntityInjectionMiddleware::class,
               
                FetchHandler::class,

            ],
            'aliases' => [

            ]

        ];

    }

    #[\Override]
    public function getDependantModules()
    {
        return [
            new InvokableProvider(\ExprAs\Doctrine\ConfigProvider::class),
            new InvokableProvider(\ExprAs\Rest\ConfigProvider::class),
        ];
    }

}
