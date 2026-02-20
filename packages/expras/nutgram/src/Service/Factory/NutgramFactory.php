<?php

namespace ExprAs\Nutgram\Service\Factory;

use ExprAs\Nutgram\Mixins\FileMixin;
use ExprAs\Nutgram\Mixins\NutgramMixin;
use ExprAs\Nutgram\RunningMode\CliRunningMode;
use ExprAs\Nutgram\RunningMode\ExprAsWebhook;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use SergiX44\Nutgram\Configuration;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Testing\FakeNutgram;
use SergiX44\Nutgram\Telegram\Types\Media\File;
use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;
use Laminas\Cache\Service\StorageAdapterFactoryInterface;

class NutgramFactory implements FactoryInterface
{

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws \ReflectionException
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $config = $container->get('config') ?? [];
        $nutgramConfig = $config['nutgram'] ?? [];

        // Use dedicated Nutgram logger
        try {
            $logger = $container->has('nutgram.logger')
                ? $container->get('nutgram.logger')
                : null;
        } catch (\Throwable $e) {
            $logger = null;
        }

        try {
            $cacheConfig = $nutgramConfig['cacheConfig'] ?? [];
            if (is_array($cacheConfig) && !empty($cacheConfig)) {
                $storageFactory = $container->get(StorageAdapterFactoryInterface::class);
                $cache = new SimpleCacheDecorator($storageFactory->createFromArrayConfiguration($cacheConfig));
            } else {
                $cache = $container->has('Laminas\Cache\Storage')
                    ? new SimpleCacheDecorator($container->get('Laminas\Cache\Storage'))
                    : '';
            }
        } catch (\Throwable) {
            $cache = ''; // Empty string for no cache
        }

        if (!isset($nutgramConfig['cache'])) {
            $nutgramConfig['cache'] = $cache;
        }

        if (!isset($nutgramConfig['logger'])) {
            $nutgramConfig['logger'] =  $logger;
        }

        if (!isset($nutgramConfig['container'])) {
            $nutgramConfig['container'] = $container;
        }

        // Create advanced configuration
        $configuration = Configuration::fromArray($nutgramConfig);

        // Check if we're in testing mode
        if (defined('PHPUNIT_RUNNING') && constant('PHPUNIT_RUNNING')) {
            return Nutgram::fake(config: $configuration);
        }

        $bot = new Nutgram($nutgramConfig['token'] ?? FakeNutgram::TOKEN, $configuration);

        if (PHP_SAPI === 'cli') {
            $cliMode = $container->get(CliRunningMode::class);
            $bot->setRunningMode($cliMode);
        } else {
            $webhookMode = $container->get(ExprAsWebhook::class);
            $bot->setRunningMode($webhookMode);
            if ($nutgramConfig['safeMode'] ?? false) {
                $webhookMode->setSafeMode(true);
                File::mixin(new FileMixin());
            }
        }

        // Apply mixins if enabled
        if ($nutgramConfig['mixins'] ?? false) {
            Nutgram::mixin(new NutgramMixin());
        }

        return $bot;
    }
}
