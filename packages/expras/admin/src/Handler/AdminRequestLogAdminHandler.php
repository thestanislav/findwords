<?php

namespace ExprAs\Admin\Handler;

use Doctrine\ORM\EntityManager;
use ExprAs\Admin\Entity\AdminRequestLogEntity;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Admin Request Log Admin Handler
 * 
 * Provides admin actions for admin request logs (truncate, etc.)
 */
class AdminRequestLogAdminHandler extends JsonServerRestApiHandler
{
    use ServiceContainerAwareTrait;

    /**
     * Truncate admin request logs table
     */
    public function truncateAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get(EntityManager::class);
        $cmd = $em->getClassMetadata(AdminRequestLogEntity::class);
        $connection = $em->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
        $connection->executeStatement($q);
        
        return new JsonResponse([
            'success' => true
        ]);
    }
}

