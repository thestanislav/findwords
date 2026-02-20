<?php

declare(strict_types=1);

namespace ExprAs\Nutgram\Console\Factory;

use ExprAs\Nutgram\Console\DeleteScheduledMessage;
use Psr\Container\ContainerInterface;

class DeleteScheduledMessageFactory
{
    public function __invoke(ContainerInterface $container): DeleteScheduledMessage
    {
        $command = new DeleteScheduledMessage();
        $command->setContainer($container);
        return $command;
    }
}
