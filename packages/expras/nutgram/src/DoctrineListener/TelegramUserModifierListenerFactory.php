<?php

declare(strict_types=1);

namespace ExprAs\Nutgram\DoctrineListener;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class TelegramUserModifierListenerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new TelegramUserModifierListener($container);
    }
}

