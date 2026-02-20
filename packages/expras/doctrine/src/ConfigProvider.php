<?php

namespace ExprAs\Doctrine;

use Doctrine\ORM\EntityManager;
use ExprAs\Core\ConfigAggregator\InvokableProvider;
use ExprAs\Core\ModuleConfigProvider\AbstractProvider;
use ExprAs\Doctrine\Behavior\Activatable\ActivatableInitializerMiddleware;
use ExprAs\Doctrine\Console\LoadFixtures;
use ExprAs\Doctrine\Container\DefaultCacheFactory;
use ExprAs\Doctrine\Container\DoctrineCommandsDelegator;
use ExprAs\Doctrine\Hydrator\DoctrineEntity;
use ExprAs\Doctrine\Hydrator\DoctrineEntityFactory;
use ExprAs\Doctrine\Repository\AbstractRepositoryFactory;
use ExprAs\Doctrine\Repository\RepositoryFactory;
use ExprAs\Doctrine\Service\EntityManagerInitializer;
use Gedmo\Blameable\BlameableListener;
use Gedmo\IpTraceable\IpTraceableListener;
use Gedmo\Loggable\LoggableListener;
use Gedmo\Sluggable\SluggableListener;
use Gedmo\SoftDeleteable\SoftDeleteableListener;
use Gedmo\Sortable\SortableListener;
use Gedmo\Timestampable\TimestampableListener;
use Gedmo\Translatable\TranslatableListener;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use ExprAs\Doctrine\Service\EventManagerFactory;
use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use ExprAs\Doctrine\Container\DoctrineEntityManagerDelegator;
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
                'doctrine.cache.default' => DefaultCacheFactory::class,
                DoctrineEntity::class => DoctrineEntityFactory::class,
                'gedmo.listener.blameable' => function (ContainerInterface $container, string $requestedName): BlameableListener {
                    $listener = new BlameableListener();

                    // This call configures the listener to use the attribute driver service created above; if using annotations, you will need to provide the appropriate service instead
                    $listener->setAnnotationReader($container->get('gedmo.mapping.driver.attribute'));

                    return $listener;
                },
                'gedmo.listener.ip_traceable' => function (ContainerInterface $container, string $requestedName): IpTraceableListener {
                    $listener = new IpTraceableListener();

                    // This call configures the listener to use the attribute driver service created above; if using annotations, you will need to provide the appropriate service instead
                    $listener->setAnnotationReader($container->get('gedmo.mapping.driver.attribute'));

                    return $listener;
                },
                'gedmo.listener.loggable' => function (ContainerInterface $container, string $requestedName): LoggableListener {
                    $listener = new LoggableListener();

                    // This call configures the listener to use the attribute driver service created above; if using annotations, you will need to provide the appropriate service instead
                    $listener->setAnnotationReader($container->get('gedmo.mapping.driver.attribute'));

                    return $listener;
                },
                'gedmo.listener.sluggable' => function (ContainerInterface $container, string $requestedName): SluggableListener {
                    $listener = new SluggableListener();

                    // This call configures the listener to use the attribute driver service created above; if using annotations, you will need to provide the appropriate service instead
                    $listener->setAnnotationReader($container->get('gedmo.mapping.driver.attribute'));

                    return $listener;
                },
                'gedmo.listener.soft_deleteable' => function (ContainerInterface $container, string $requestedName): SoftDeleteableListener {
                    $listener = new SoftDeleteableListener();

                    // This call configures the listener to use the attribute driver service created above; if using annotations, you will need to provide the appropriate service instead
                    $listener->setAnnotationReader($container->get('gedmo.mapping.driver.attribute'));

                    // If your application uses a PSR-20 clock, you can provide it to this listener by uncommenting the below line
                    // $listener->setClock($container->get(ClockInterface::class));

                    return $listener;
                },
                'gedmo.listener.sortable' => function (ContainerInterface $container, string $requestedName): SortableListener {
                    $listener = new SortableListener();

                    // This call configures the listener to use the attribute driver service created above; if using annotations, you will need to provide the appropriate service instead
                    $listener->setAnnotationReader($container->get('gedmo.mapping.driver.attribute'));

                    return $listener;
                },
                'gedmo.listener.timestampable' => function (ContainerInterface $container, string $requestedName): TimestampableListener {
                    $listener = new TimestampableListener();

                    // This call configures the listener to use the attribute driver service created above; if using annotations, you will need to provide the appropriate service instead
                    $listener->setAnnotationReader($container->get('gedmo.mapping.driver.attribute'));

                    // If your application uses a PSR-20 clock, you can provide it to this listener by uncommenting the below line
                    // $listener->setClock($container->get(ClockInterface::class));

                    return $listener;
                },
                'gedmo.listener.translatable' => function (ContainerInterface $container, string $requestedName): TranslatableListener {
                    $listener = new TranslatableListener();

                    // This call configures the listener to use the attribute driver service created above; if using annotations, you will need to provide the appropriate service instead
                    $listener->setAnnotationReader($container->get('gedmo.mapping.driver.attribute'));

                    // If your application uses a PSR-20 clock, you can provide it to this listener by uncommenting the below line
                    // $listener->setClock($container->get(ClockInterface::class));

                    return $listener;
                },
            ],
            'invokables' => [
                RepositoryFactory::class,
                ActivatableInitializerMiddleware::class,
                LoadFixtures::class
            ],
            
            'abstract_factories' => [AbstractRepositoryFactory::class],
            'initializers' => [
                EntityManagerInitializer::class
            ],
            'aliases' => [
                'doctrine.entity_manager.orm_default' => EntityManager::class,
                \Doctrine\ORM\EntityManagerInterface::class => 'doctrine.entity_manager.orm_default',
            ],
            'delegators' => [
                Application::class => [
                    DoctrineCommandsDelegator::class,
                ],
                DoctrineEntityManager::class => [
                    DoctrineEntityManagerDelegator::class
                ]
            ],
        ];
    }


    /**
     * @return array
     */
    #[\Override]
    public function getConfig()
    {
        /*
        $config = ArrayUtils::merge(
            require_once 'vendor/doctrine/doctrine-module/config/module.config.php',
            require_once 'vendor/doctrine/doctrine-orm-module/config/module.config.php'
        );

        $config['dependencies'] = $config['service_manager'];
        unset($config['service_manager']);


        $config['dependencies']['aliases']['configuration'] = 'config';
        $config['dependencies']['aliases']['Config'] = 'config';
        $config['dependencies']['aliases']['Configuration'] = 'config';


        return $config;
        */

        return [
            'doctrine_factories' => [
                'eventmanager' => EventManagerFactory::class,
            ],
            'dependencies' => [

            ]
        ];
    }

    #[\Override]
    public function getDependantModules()
    {
        return [
            new InvokableProvider(\DoctrineModule\ConfigProvider::class),
            new InvokableProvider(\DoctrineORMModule\ConfigProvider::class),
            new InvokableProvider(\ExprAs\Core\ConfigProvider::class),
        ];
    }
}
