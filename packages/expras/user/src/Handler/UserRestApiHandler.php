<?php

namespace ExprAs\User\Handler;

use ExprAs\Admin\Handler\JsonServerRestApiHandler;
use ExprAs\User\Entity\UserSuper;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserRestApiHandler extends JsonServerRestApiHandler
{
    #[\Override]
    public function createAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        $entityName = $this->getEntityName();
        $entity = new $entityName();

        $post = $this->getBodyParams()->getArrayCopy();
        $post['password'] = password_hash((string) $this->getBodyParams()->get('password'), PASSWORD_BCRYPT);

        $dqlQuery = $this->getEntityManager()->createQuery(sprintf('select e from %s e where e.email = :email', $entityName));
        $dqlQuery->setParameters(['email' => $this->getBodyParams()->get('email')]);
        if ($dqlQuery->getOneOrNullResult()) {
            return new JsonResponse(
                [
                'message' => 'Email используется'
                ], 500
            );
        }

        $dqlQuery = $this->getEntityManager()->createQuery(sprintf('select e from %s e where e.username = :username', $entityName));
        $dqlQuery->setParameters(['username' => $this->getBodyParams()->get('username')]);
        if ($dqlQuery->getOneOrNullResult()) {
            return new JsonResponse(
                [
                'message' => 'Имя пользователя используется'
                ], 500
            );
        }

        unset($post['id']);
        /**
 * @var UserSuper $entity 
*/
        $entity = $this->getHydrator()->hydrate($post, $entity);

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return new JsonResponse($this->getHydrator()->extract($entity));
    }

    #[\Override]
    public function updateAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        $entityName = $this->getEntityName();
        if (!($entity = $this->getEntityManager()->find($entityName, $request->getAttribute('entity_id')))) {
            return $delegate->handle($request);
        }

        $post = $this->getBodyParams()->getArrayCopy();
        if ($this->getBodyParams()->get('password')) {
            $post['password'] = password_hash((string) $this->getBodyParams()->get('password'), PASSWORD_BCRYPT);
        } else {
            unset($post['password']);
        }

        if ($this->getBodyParams()->get('email') != $entity->getEmail()) {
            $dqlQuery = $this->getEntityManager()->createQuery(sprintf('select e from %s e where e.email = :email and e.id <> :id', $entityName));
            $dqlQuery->setParameters(['email' => $this->getBodyParams()->get('email'), 'id' => $entity->getId()]);
            if ($dqlQuery->getOneOrNullResult()) {
                return new JsonResponse(
                    [
                    'message' => 'Email используется'
                    ], 500
                );
            }
        }

        if ($this->getBodyParams()->get('username') != $entity->getUsername()) {
            $dqlQuery = $this->getEntityManager()->createQuery(sprintf('select e from %s e where e.username = :username and e.id <> :id', $entityName));
            $dqlQuery->setParameters(['username' => $this->getBodyParams()->get('username'), 'id' => $entity->getId()]);
            if ($dqlQuery->getOneOrNullResult()) {
                return new JsonResponse(
                    [
                    'message' => 'Имя пользователя используется'
                    ], 500
                );
            }
        }

        unset($post['id']);
        /**
 * @var UserSuper $entity 
*/
        $entity = $this->getHydrator()->hydrate($post, $entity);

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return new JsonResponse($this->getHydrator()->extract($entity));
    }

}
