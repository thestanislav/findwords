<?php

namespace ExprAs\Nutgram\Mezzio\Handler;

use ExprAs\Nutgram\Service\TelegramAuthService;
use Psr\Container\ContainerInterface;

class TelegramAuthRedirectHandlerFactory
{
    public function __invoke(ContainerInterface $container): TelegramAuthRedirectHandler
    {
        $authService = $container->get(TelegramAuthService::class);
        return new TelegramAuthRedirectHandler($authService);
    }
}

