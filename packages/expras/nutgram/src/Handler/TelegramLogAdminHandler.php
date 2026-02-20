<?php

namespace ExprAs\Nutgram\Handler;

use Doctrine\ORM\EntityManager;
use ExprAs\Admin\Handler\JsonServerRestApiHandler;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use ExprAs\Nutgram\Entity\TelegramLogEntity;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Telegram Log Admin Handler
 * 
 * Provides admin actions for Telegram logs (truncate, etc.)
 */
class TelegramLogAdminHandler extends JsonServerRestApiHandler
{
    use ServiceContainerAwareTrait;

    /**
     * Truncate telegram logs table
     */
    public function truncateAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get(EntityManager::class);
        $cmd = $em->getClassMetadata(TelegramLogEntity::class);
        $connection = $em->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
        $connection->executeStatement($q);
        
        return new JsonResponse([
            'success' => true
        ]);
    }
}

