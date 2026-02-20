<?php

namespace ExprAs\Nutgram\Service\Factory;

use ExprAs\Nutgram\Service\TelegramAuthService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

class TelegramAuthServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        // Get nutgram configuration
        $config = $container->get('config');
        $nutgramConfig = $config['nutgram'] ?? [];
        $authConfig = $nutgramConfig['auth'] ?? [];

        // Get dependencies
        $cache = $container->get(CacheInterface::class);
        $botUsername = $authConfig['bot_username'] ?? '';
        $defaultTtl = $authConfig['token_ttl'] ?? 900; // 15 minutes default

        return new TelegramAuthService($cache, $botUsername, $defaultTtl);
    }
}
