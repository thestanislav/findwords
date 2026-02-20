<?php

/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/11/2017
 * Time: 15:26
 */

namespace ExprAs\Nutgram\Middleware;

use Doctrine\ORM\EntityManager;
use ExprAs\Doctrine\Hydrator\DoctrineEntity;
use ExprAs\Doctrine\Repository\DefaultRepository;
use ExprAs\Nutgram\Entity\User;
use SergiX44\Nutgram\Nutgram;

class UserEntityInjectorMiddleware
{
    public function __invoke(Nutgram $bot, $next)
    {
        if (($user = $bot->update()?->getUser())) {
            $container = $bot->getContainer();
            
            /**
             * @var EntityManager $em
             */
            $em = $container->get(EntityManager::class);
            
            /**
             * @var DefaultRepository $repo
             */
            $entityClass = $container->get('config')['nutgram']['userEntity'];

            /**
             * @var $userEntity User
             */
            if (!($userEntity = $em->find($entityClass, $user->id))) {
                $userEntity = new $entityClass();
                $userEntity->setId($user->id);
            }
            
            /**
             * @var DoctrineEntity $hydrator
             */
            $hydrator = $container->get(DoctrineEntity::class);
            $hydrator->hydrate($user->toArray(), $userEntity);
            $em->persist($userEntity);
            $em->flush($userEntity);

            $bot->set(User::class, $userEntity);
            $bot->set($entityClass, $userEntity);
            $bot->set('expras.nutgram.user', $userEntity);
        }

        $next($bot);
    }
}
