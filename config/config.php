<?php

declare(strict_types=1);

use ExprAs\Core\ConfigAggregator\InvokableProvider;
use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

// To enable or disable caching, set the `ConfigAggregator::ENABLE_CACHE` boolean in
// `config/autoload/local.php`, or use CONFIG_CACHE_ENABLED env var (see env-overrides.php).
$cacheConfig = [
    'config_cache_path' => [
        'web' => 'data/cache/config-cache-web.php',
        'cli' => 'data/cache/config-cache-cli.php',
    ],
];

$aggregator = new ConfigAggregator([
    \Mimmi20\LaminasView\Helper\HtmlElement\ConfigProvider::class,
    \Laminas\Serializer\ConfigProvider::class,
    \Mimmi20\Mezzio\Navigation\LaminasView\ConfigProvider::class,
    \Laminas\Cache\Storage\Adapter\Memcached\ConfigProvider::class,
    \Laminas\Cache\Storage\Adapter\BlackHole\ConfigProvider::class,
    \Mezzio\Authentication\ConfigProvider::class,
    \Laminas\Session\ConfigProvider::class,
    \Laminas\I18n\ConfigProvider::class,
    \Mimmi20\Mezzio\Navigation\ConfigProvider::class,
    \AlexTartan\Mezzio\SymfonyConsole\ConfigProvider::class,
    \DoctrineORMModule\ConfigProvider::class,
    \DoctrineModule\ConfigProvider::class,
    \Laminas\Cache\Storage\Adapter\Memory\ConfigProvider::class,
    \Laminas\Cache\Storage\Adapter\Filesystem\ConfigProvider::class,
    \Laminas\Cache\ConfigProvider::class,
    \Laminas\Form\ConfigProvider::class,
    \Laminas\Hydrator\ConfigProvider::class,
    \Laminas\InputFilter\ConfigProvider::class,
    \Laminas\Filter\ConfigProvider::class,
    \Laminas\Router\ConfigProvider::class,
    \Laminas\Validator\ConfigProvider::class,
    \Laminas\Paginator\ConfigProvider::class,
    class_exists(\Mezzio\Tooling\ConfigProvider::class) ? \Mezzio\Tooling\ConfigProvider::class : fn() => [],
    \Mezzio\LaminasView\ConfigProvider::class,
    \Mezzio\Helper\ConfigProvider::class,
    \Mezzio\Router\FastRouteRouter\ConfigProvider::class,
    \Laminas\HttpHandlerRunner\ConfigProvider::class,
    // Include cache configuration
    new ArrayProvider($cacheConfig),
    \Mezzio\ConfigProvider::class,
    \Mezzio\Router\ConfigProvider::class,
    \Laminas\Diactoros\ConfigProvider::class,
  
    // Default App module config
    new InvokableProvider(\ExprAs\Helpers\ConfigProvider::class),
    new InvokableProvider(\App\ConfigProvider::class),

    \Mezzio\Swoole\ConfigProvider::class,

    // Load application config in a pre-defined order in such a way that local settings
    // overwrite global settings. (Loaded as first to last):
    //   - `global.php`
    //   - `*.global.php`
    //   - `local.php`
    //   - `*.local.php`
    new PhpFileProvider(realpath(__DIR__) . '/autoload/{{,*.}global,{,*.}local}.php'),
    // Load development config if it exists
    new PhpFileProvider(realpath(__DIR__) . '/development.config.php'),
], $cacheConfig['config_cache_path'][PHP_SAPI === 'cli' ? 'cli' : 'web']);

return $aggregator->getMergedConfig();
