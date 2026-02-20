<?php

namespace ExprAs\Rest\Helper;

use Doctrine\ORM\EntityManager;
use ExprAs\Doctrine\Hydrator\DoctrineEntity;
use Laminas\Hydrator\HydratorPluginManager;
use Mezzio\Helper\ServerUrlHelper;
use Mezzio\Helper\UrlHelper;
use Psr\Container\ContainerInterface;

class ExcelExportHelperFactory
{
    public function __invoke(ContainerInterface $container): ExcelExportHelper
    {
        $entityManager = $container->get(EntityManager::class);
        $hydratorManager = $container->get(HydratorPluginManager::class);
        $hydrator = $hydratorManager->get(DoctrineEntity::class);
        $urlHelper = $container->get(UrlHelper::class);
        
        // ServerUrlHelper is optional - may not be available in all contexts
        $serverUrlHelper = null;
        if ($container->has(ServerUrlHelper::class)) {
            $serverUrlHelper = $container->get(ServerUrlHelper::class);
        }
        
        return new ExcelExportHelper($entityManager, $hydrator, $urlHelper, $serverUrlHelper);
    }
}
