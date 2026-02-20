<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/9/2017
 * Time: 11:13
 */

namespace ExprAs\Core\ConfigAggregator;

use Laminas\ConfigAggregator\InvalidConfigProviderException;
use Laminas\Stdlib\ArrayUtils;

class InvokableProvider
{
    /**
     * @var callable $_provider
     */
    protected $_provider;

    /**
     * @var callable|null $_postProcessor
     */
    protected $_postProcessor;

    /**
     * RecursiveProvider constructor.
     *
     * @param $_provider
     * @param callable|null $postProcessor
     */
    public function __construct($_provider, ?callable $postProcessor = null)
    {
        $this->_provider = $_provider;
        $this->_postProcessor = $postProcessor;
    }

    /**
     * Resolve a provider.
     *
     * If the provider is a string class name, instantiates that class and
     * tests if it is callable, returning it if true.
     *
     * If the provider is a callable, returns it verbatim.
     *
     * Raises an exception for any other condition.
     *
     * @param  string|callable $provider
     * @return callable
     * @throws InvalidConfigProviderException
     */
    private function resolveProvider($provider)
    {
        if (is_string($provider)) {
            if (!class_exists($provider)) {
                throw new InvalidConfigProviderException("Cannot read config from $provider - class cannot be loaded.");
            }
            $provider = new $provider();
        }

        if (!is_callable($provider)) {
            throw new InvalidConfigProviderException(
                sprintf("Cannot read config from %s - config provider must be callable.", $provider::class)
            );
        }

        return $provider;
    }


    private function mergeProvidersRecursive($a, $b)
    {
        $out = $a;
        foreach ($b as $key => $value) {
            if ($value instanceof self) {
                $value = $value();
                $out = $this->mergeProvidersRecursive($out, $value);
            } else {
                if (isset($out[$key]) && is_array($out[$key])) {
                    $out[$key] = ArrayUtils::merge($out[$key], $value);
                } elseif (!is_numeric($key)) {
                    $out[$key] = $value;
                } else {
                    $out[] = $value;
                }

            }
        }

        return $out;
    }


    /**
     * @return \Generator
     */
    public function __invoke()
    {
        $mergedConfig = [];
        $provider = $this->resolveProvider($this->_provider);
        $config = $provider();
        if ($config instanceof \Generator) {
            $config = iterator_to_array($config);
        }
        $mergedConfig = $this->mergeProvidersRecursive($mergedConfig, $config);

        if ($this->_postProcessor !== null) {
            $postProcessor = $this->resolveProvider($this->_postProcessor);
            $postConfig = $postProcessor();
            $mergedConfig = $this->mergeProvidersRecursive($mergedConfig, $postConfig);
        }

        return $mergedConfig;
    }
}
