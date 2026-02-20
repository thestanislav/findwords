<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/9/2017
 * Time: 00:00
 */

namespace ExprAs\Admin;

use ExprAs\Admin\Factory\AppRoutesDelegatorFactory;
use ExprAs\Admin\Factory\JsonApiFieldExcludeConfiguratorFactory;
use ExprAs\Admin\Factory\ResourceMappingConfigurationFactory;
use ExprAs\Admin\Factory\SelectChoiceLabelExtractorConfiguratorFactory;
use ExprAs\Admin\Handler\AdminLayoutHandler;
use ExprAs\Admin\Handler\AuthenticationHandler;
use ExprAs\Admin\Handler\JsonServerRestApiHandlerAbstractFactory;
use ExprAs\Admin\Handler\JsonServerRestApiFactory;
use ExprAs\Admin\Handler\JsonServerRestApiHandler;
use ExprAs\Admin\HydratorConfigurator\JsonApiFieldExcludeConfigurator;
use ExprAs\Admin\HydratorConfigurator\SelectChoiceLabelExtractorConfigurator;
use ExprAs\Admin\Middleware\AdminAuthenticationMiddleware;
use ExprAs\Admin\Middleware\AdminAuthenticationMiddlewareFactory;
use ExprAs\Admin\Middleware\AdminIsGrantedMiddleware;
use ExprAs\Admin\Middleware\AdminApiLoggingMiddleware;
use ExprAs\Admin\Middleware\AdminApiLoggingMiddlewareFactory;
use ExprAs\Admin\ResourceMapping\Configuration;
use ExprAs\Admin\Service\AdminContextProcessor;
use ExprAs\Admin\Service\AdminContextProcessorFactory;
use ExprAs\Admin\Handler\AdminDoctrineHandler;
use ExprAs\Admin\Handler\AdminDoctrineHandlerFactory;
use ExprAs\Admin\DoctrineListener\AdminLogEntityModifierListener;
use ExprAs\AdminGenerator\Console\GenerateEntityConfig;
use ExprAs\Core\ConfigAggregator\InvokableProvider;
use ExprAs\Core\ModuleConfigProvider\AbstractProvider;
use Laminas\ConfigAggregator\PhpFileProvider;
use Laminas\Stdlib\ArrayUtils;
use ExprAs\Logger\Service\LoggerAbstractFactory;
use ExprAs\Admin\Handler\AdminRequestLogAdminHandler;
use Mezzio\Application;

class ConfigProvider extends AbstractProvider
{
    /**
     * Returns the container dependencies
     *
     * @return array
     */
    #[\Override]
    public function getDependencies(): array
    {
        return [
            'abstract_factories' => [
                JsonServerRestApiHandlerAbstractFactory::class,
            ],
            'factories' => [
                SelectChoiceLabelExtractorConfigurator::class => SelectChoiceLabelExtractorConfiguratorFactory::class,
                JsonApiFieldExcludeConfigurator::class => JsonApiFieldExcludeConfiguratorFactory::class,
                AdminAuthenticationMiddleware::class => AdminAuthenticationMiddlewareFactory::class,
                AdminApiLoggingMiddleware::class => AdminApiLoggingMiddlewareFactory::class,
                JsonServerRestApiHandler::class => JsonServerRestApiFactory::class,
                Configuration::class => ResourceMappingConfigurationFactory::class,

                // Logger infrastructure
                'admin.api.logger' => LoggerAbstractFactory::class,
                AdminContextProcessor::class => AdminContextProcessorFactory::class,
                AdminDoctrineHandler::class => AdminDoctrineHandlerFactory::class,
            ],
            'invokables' => [
                AdminLayoutHandler::class,
                AuthenticationHandler::class,
                GenerateEntityConfig::class,
                AdminIsGrantedMiddleware::class,
                AdminRequestLogAdminHandler::class,
                AdminLogEntityModifierListener::class,
            ],
            'delegators' => [
                Application::class => [
                    AppRoutesDelegatorFactory::class,
                ],
            ],
        ];
    }

    #[\Override]
    public function getDependantModules()
    {
        $modules = [
            new InvokableProvider(\ExprAs\User\ConfigProvider::class),
            new InvokableProvider(\ExprAs\Uploadable\ConfigProvider::class),
        ];
        if (class_exists(\ExprAs\AdminGenerator\ConfigProvider::class, false)) {
            $modules[] = new InvokableProvider(\ExprAs\AdminGenerator\ConfigProvider::class);
        }
        return $modules;
    }
}