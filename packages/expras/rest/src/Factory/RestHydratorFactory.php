<?php

namespace ExprAs\Rest\Factory;

use Doctrine\ORM\EntityManager;
use ExprAs\Rest\Hydrator\Configurator\RestHydratorConfiguratorInterface;
use ExprAs\Rest\Hydrator\RestHydrator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Stdlib\SplPriorityQueue;
use Psr\Container\ContainerInterface;

class RestHydratorFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName = RestHydrator::class, ?array $options = null)
    {
        $hydrator = new $requestedName($container->get(EntityManager::class));

        if ($options) {
            $createPriorityQueueReducer = function (): callable {
                // $serial is used to ensure that items of the same priority are enqueued
                // in the order in which they are inserted.
                $serial = PHP_INT_MAX;
                return function ($queue, $item) use (&$serial) {
                    $priority = is_array($item) && isset($item['priority']) && is_int($item['priority'])
                        ? $item['priority']
                        : 1;
                    $queue->insert(is_array($item) ? $item['configurator'] : $item, [$priority, $serial]);
                    $serial -= 1;
                    return $queue;
                };
            };
            $queue = array_reduce(
                $options,
                $createPriorityQueueReducer(),
                new SplPriorityQueue()
            );

            foreach ($queue as $_configurator) {
                if (is_string($_configurator)) {
                    $instance = $container->has($_configurator) ? $container->get($_configurator) : new $_configurator();
                }else{
                    $instance = $_configurator;
                }

                if (!($instance instanceof RestHydratorConfiguratorInterface)) {
                    throw new \InvalidArgumentException($instance::class . ' is expected to be instance of ' . RestHydratorConfiguratorInterface::class);
                }
                $hydrator->addConfigurator($instance);
            }
        }

        return $hydrator;
    }

}
