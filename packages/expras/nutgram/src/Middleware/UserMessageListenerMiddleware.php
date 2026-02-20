<?php

namespace ExprAs\Nutgram\Middleware;

use Doctrine\ORM\EntityManager;
use ExprAs\Doctrine\Hydrator\DoctrineEntity;
use ExprAs\Nutgram\Entity\User;
use ExprAs\Nutgram\Entity\UserMessage;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Common\Update;

class UserMessageListenerMiddleware
{
    public function __invoke(Nutgram $bot, $next)
    {
        $update = $bot->update();
        $from = null;

        $userId = $bot->userId();

        // Check different update types for user information
        foreach ([
            'message' => 'from',
            'callback_query' => 'from',
            'poll_answer' => 'user',
            'channel_post' => 'from',
        ] as $action => $key) {
            if ($update?->{$action} && ($from = $update->{$action}->{$key})) {
                break;
            }
        }

        if ($from) {
            $container = $bot->getContainer();
            /** @var EntityManager $em */
            $em = $container->get(EntityManager::class);

            // Get the user entity that was injected by UserEntityInjectorMiddleware
            /** @var DefaultUser $user */
            $user = $bot->get(User::class);

            if ($user) {
                /** @var DoctrineEntity $hydrator */
                $hydrator = $container->get(DoctrineEntity::class);

                $userMessage = new UserMessage();
                $userMessage->setUser($user);

                // Handle different message types
                if ($update->message) {
                    $userMessage->setTextMessage($update->message->text);
                    $userMessage->setUpdateType('message');
                }elseif($update->callback_query){
                    $userMessage->setTextMessage($update->callback_query->data);
                    $userMessage->setUpdateType('callback_query');
                }elseif($update->channel_post){
                    $userMessage->setTextMessage($update->channel_post->text);
                    $userMessage->setUpdateType('channel_post');
                }elseif($update->poll_answer){
                    $userMessage->setTextMessage(implode(', ', $update->poll_answer->option_ids));
                    $userMessage->setUpdateType('poll_answer');
                }

                $userMessage->setMessageObject($update->toArray());

                $em->persist($userMessage);
                $em->flush($userMessage);
            }
        }

        $next($bot);
    }
}
