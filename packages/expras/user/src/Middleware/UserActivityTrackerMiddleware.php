<?php

namespace ExprAs\User\Middleware;

use Doctrine\ORM\EntityManager;
use ExprAs\User\Entity\UserSuper;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserActivityTrackerMiddleware implements MiddlewareInterface
{
    public function __construct(protected EntityManager $entityManager)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /**
         * @var UserSuper|null $user
         */
        if (($user = $request->getAttribute(UserInterface::class))) {
            $user->setLastActivityAt(new \DateTimeImmutable());
            $this->entityManager->persist($user);
            $this->entityManager->flush($user);
        }

        return $handler->handle($request);
    }
}

