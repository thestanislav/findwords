<?php

declare(strict_types=1);

namespace ExprAs\Doctrine\Service;

use Doctrine\ORM\EntityManagerInterface;
use ExprAs\Core\ServiceManager\AbstractTraitInitializer;
use Psr\Container\ContainerInterface;

class EntityManagerInitializer extends AbstractTraitInitializer
{
    public function __invoke(ContainerInterface $container, $instance): void
    {
        if (!is_object($instance)) {
            return;
        }

        if (in_array(EntityManagerAwareTrait::class, class_uses($instance), true)) {
            $instance->setEntityManager($container->get(EntityManagerInterface::class));
        }
    }
}
