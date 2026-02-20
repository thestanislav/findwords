<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/9/2017
 * Time: 23:57
 */

namespace ExprAs\Core\ServiceManager\Factory;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;

final class EntityManagerInvokableFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $em = $container->get(EntityManager::class);
        return (null === $options) ? new $requestedName($em) : new $requestedName($em, $options);

    }
}
