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
use ExprAs\Nutgram\Entity\Chat;
use SergiX44\Nutgram\Nutgram;

class ChatEntityInjectorMiddleware
{
    public function __invoke(Nutgram $bot, $next)
    {
        if (($chat = $bot->update()->getChat())) {
            $container = $bot->getContainer();

            /**
             * @var EntityManager $em
             */
            $em = $container->get(EntityManager::class);

            /**
             * @var DefaultRepository $repo
             */
            $entityClass = $container->get('config')['nutgram']['chatEntity'];

            /**
             * @var $chatEntity Chat
             */
            if (!($chatEntity = $em->find($entityClass, $chat->id))) {
                $chatEntity = new $entityClass();
                $chatEntity->setId($chat->id);
            }
            
            /**
             * @var DoctrineEntity $hydrator
             */
            $hydrator = $container->get(DoctrineEntity::class);
            $hydrator->hydrate($chat->toArray(), $chatEntity);
            $em->persist($chatEntity);
            $em->flush($chatEntity);

            $bot->set(Chat::class, $chatEntity);
            $bot->set('expras.nutgram.chat', $chatEntity);
        }

        $next($bot);
    }
}
