<?php
declare(strict_types=1);

use ExprAs\Nutgram\Console\HookInfoCommand;
use ExprAs\Nutgram\Console\HookRemoveCommand;
use ExprAs\Nutgram\Console\HookSetCommand;
use ExprAs\Nutgram\Console\LogoutCommand;
use ExprAs\Nutgram\Console\RegisterCommandsCommand;
use ExprAs\Nutgram\Console\SendScheduledMessage;
use ExprAs\Nutgram\Console\UpdateScheduledMessage;
use ExprAs\Nutgram\Console\DeleteScheduledMessage;


return [
    'mezzio-symfony-console' => [
        'commands' => [
            HookInfoCommand::class,
            HookRemoveCommand::class,
            HookSetCommand::class,
            LogoutCommand::class,
            RegisterCommandsCommand::class,
            SendScheduledMessage::class,
            UpdateScheduledMessage::class,
            DeleteScheduledMessage::class,
        ],
    ],
];
