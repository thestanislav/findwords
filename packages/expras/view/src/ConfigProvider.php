<?php

declare(strict_types=1);

namespace ExprAs\View;

use ExprAs\Core\ModuleConfigProvider\AbstractProvider;
use ExprAs\Core\ServiceManager\Factory\ContainerInvokableFactory;
use ExprAs\View\Helper\ProfiledPaginationControlMiddleware;
use ExprAs\View\Helper\RouteMatchFactory;
use ExprAs\View\Middleware\ResetViewHelperStateMiddleware;
use ExprAs\View\Middleware\ResetViewHelperStateMiddlewareFactory;

class ConfigProvider extends AbstractProvider
{
    #[\Override]
    public function getDependencies()
    {
        return [
            'factories' => [
                ProfiledPaginationControlMiddleware::class => ContainerInvokableFactory::class,
                ResetViewHelperStateMiddleware::class => ResetViewHelperStateMiddlewareFactory::class,
            ],
            'invokables' => [
                RouteMatchFactory::class,
            ]
        ];
    }
}
