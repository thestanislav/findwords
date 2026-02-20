<?php

namespace ExprAs\Core\ServiceManager\Factory;

use Interop\Container\ContainerInterface;
use Laminas\EventManager\EventManager;

class EventManagerFactory
{
    /**
     * Create an EventManager instance
     *
     * Creates a new EventManager instance, seeding it with a shared instance
     * of SharedEventManager.
     *
     * @param  string     $name
     * @param  null|array $options
     * @return EventManager
     */
    public function __invoke(ContainerInterface $container, $name, ?array $options = null)
    {
        $shared = $container->has('SharedEventManager') ? $container->get('SharedEventManager') : null;

        return new EventManager($shared);
    }
}
