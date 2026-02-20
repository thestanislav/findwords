<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 14.04.2014
 * Time: 11:36
 */

namespace ExprAs\Doctrine\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Container\ContainerInterface;

class AbstractRepositoryFactory implements AbstractFactoryInterface
{
    protected $_map = [];

    protected $_entityManager;

    public function canCreate(ContainerInterface $container, $requestedName)
    {

        return !is_null($this->getRepository($container, $requestedName));
    }

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {

        if (!($repository = $this->canCreate($container, $requestedName))) {
            throw new ServiceNotFoundException();
        }

        return $this->getRepository($container, $requestedName);
    }


    private function getRepository(ContainerInterface $container, $name)
    {

        if (isset($this->_map[$name])) {
            return $this->_map[$name];
        }

        $match = [];

        if (preg_match('~^([A-Z][A-Za-z]+)\\\\([A-Z][A-Za-z\\\\]+)\\\\Repository$~', (string) $name, $match)) {
            $entity = sprintf('%s\Entity\%s', $match[1], $match[2]);
            if (class_exists($entity)) {
                $em = $container->get(EntityManager::class);
                return $this->_map[$name] = $em->getRepository($entity);
            }
        }
        return null;
    }
}
