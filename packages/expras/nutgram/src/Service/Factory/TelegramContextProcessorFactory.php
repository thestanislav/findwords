<?php

namespace ExprAs\Nutgram\Service\Factory;

use ExprAs\Nutgram\Service\TelegramContextProcessor;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use SergiX44\Nutgram\Nutgram;

/**
 * Factory for TelegramContextProcessor
 */
class TelegramContextProcessorFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): TelegramContextProcessor
    {
        return new TelegramContextProcessor(
            $container->get(Nutgram::class)
        );
    }
}

