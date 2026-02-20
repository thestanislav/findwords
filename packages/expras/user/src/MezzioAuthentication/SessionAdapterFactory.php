<?php

namespace ExprAs\User\MezzioAuthentication;

use Doctrine\ORM\EntityManager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class SessionAdapterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $entityName = $container->get('config')['expras-user']['entity_name'];
        /**
 * @var EntityManager $em 
*/
        $em = $container->get(EntityManager::class);
        return new SessionAdapter(
            $em->getRepository($entityName),
            $container->get(ResponseInterface::class),
            $container->get(SessionContainer::class)
        );
    }
}
