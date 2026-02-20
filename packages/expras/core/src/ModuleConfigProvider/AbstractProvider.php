<?php

namespace ExprAs\Core\ModuleConfigProvider;

use Laminas\ConfigAggregator\PhpFileProvider;
use Laminas\Stdlib\ArrayUtils;

abstract class AbstractProvider
{
    protected static $_configProvidedModules = [];

    public function __invoke()
    {
        // prevent loading modules multiple times
        if (false !== array_search(static::class, self::$_configProvidedModules)) {
            return [];
        }
        self::$_configProvidedModules[] = static::class;

        $config = [];
        $config = ArrayUtils::merge($config, ['dependencies' => $this->getDependencies()]);
        $reflection = new \ReflectionObject($this);
        foreach ((new PhpFileProvider(dirname($reflection->getFileName()) . '/../config/*.php'))() as $_config) {
            $config = ArrayUtils::merge($config, $_config, true);
        }

        $config = ArrayUtils::merge($config, $this->getConfig());
        if ($modules = $this->getDependantModules()) {
            array_unshift($config, ...$modules);
        }

        return $config;
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies()
    {
        return [];
    }

    /**
     * Returns the config providers of dependant modules
     *
     * @return array
     */
    public function getDependantModules()
    {
        return [];
    }

    /**
     * Returns the additional configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [];
    }
}
