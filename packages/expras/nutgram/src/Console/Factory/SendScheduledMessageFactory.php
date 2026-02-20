<?php

declare(strict_types=1);

namespace ExprAs\Nutgram\Console\Factory;

use ExprAs\Nutgram\Console\SendScheduledMessage;
use Psr\Container\ContainerInterface;

class SendScheduledMessageFactory
{
    public function __invoke(ContainerInterface $container): SendScheduledMessage
    {
        $command = new SendScheduledMessage();
        $command->setContainer($container);
        return $command;
    }
}
