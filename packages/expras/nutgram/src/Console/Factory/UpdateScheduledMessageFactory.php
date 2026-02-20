<?php

declare(strict_types=1);

namespace ExprAs\Nutgram\Console\Factory;

use ExprAs\Nutgram\Console\UpdateScheduledMessage;
use Psr\Container\ContainerInterface;

class UpdateScheduledMessageFactory
{
    public function __invoke(ContainerInterface $container): UpdateScheduledMessage
    {
        $command = new UpdateScheduledMessage();
        $command->setContainer($container);
        return $command;
    }
}
