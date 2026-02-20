<?php

namespace ExprAs\Logger\Api;

use Doctrine\ORM\EntityManager;
use ExprAs\Admin\Handler\JsonServerRestApiHandler;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use ExprAs\Logger\Entity\ErrorLogEntity;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ErrorLogAdminHandler extends JsonServerRestApiHandler
{
    use ServiceContainerAwareTrait;


    public function truncateAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        /**
 * @var EntityManager $em 
*/
        $em = $this->getContainer()->get(EntityManager::class);
        $cmd = $em->getClassMetadata(ErrorLogEntity::class);
        $connection = $em->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
        $connection->executeUpdate($q);
        return new JsonResponse(
            [
            'success' => true
            ]
        );
    }

}
