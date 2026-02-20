<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 2/20/2018
 * Time: 16:27
 */

namespace ExprAs\Doctrine\Service;

use Doctrine\ORM\EntityManager;
use ExprAs\Core\ServiceManager\AbstractTraitInitializer;
use Psr\Container\ContainerInterface;

class EntityManagerInitializer extends AbstractTraitInitializer
{
    public function __invoke(ContainerInterface $container, $instance)
    {
        if (!is_object($instance)) {
            return;
        }

        if (in_array(EntityManagerAwareTrait::class, class_uses($instance))) {
            $instance->setEntityManager($container->get(EntityManager::class));
        }
    }
}
