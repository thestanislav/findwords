<?php

/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/9/2017
 * Time: 15:15
 */

use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use ExprAs\Nutgram\DoctrineListener\BotUserModifierListener;
use ExprAs\Nutgram\DoctrineListener\TelegramLogEntityModifierListener;
use ExprAs\Nutgram\DoctrineListener\TelegramUserModifierListener;

return [
    'doctrine' => [
        'driver' => [
            'nutgram_entity_driver' => [
                'class' => AttributeDriver::class,
                'paths' => [
                    __DIR__ . '/../src/Entity'
                ],
            ],
            'orm_default'                   => [
                'drivers' => [
                    ExprAs\Nutgram\Entity\DefaultUser::class => 'nutgram_entity_driver',
                    ExprAs\Nutgram\Entity\DefaultChat::class => 'nutgram_entity_driver',
                    ExprAs\Nutgram\Entity\UserMessage::class => 'nutgram_entity_driver',
                    ExprAs\Nutgram\Entity\ScheduledMessage::class => 'nutgram_entity_driver',
                    ExprAs\Nutgram\Entity\ScheduledMessageSentStatus::class => 'nutgram_entity_driver',
                    ExprAs\Nutgram\Entity\MessageToUser::class => 'nutgram_entity_driver',
                    ExprAs\Nutgram\Entity\TelegramLogEntity::class => 'nutgram_entity_driver',

                ],

            ],

        ],
        'eventmanager'  => [
            'orm_default' => [
                'subscribers' => [
                    BotUserModifierListener::class,
                    TelegramLogEntityModifierListener::class,
                    TelegramUserModifierListener::class,
                ],
            ],
        ],
    ]
];
