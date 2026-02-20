<?php

namespace ExprAs\Admin\ResourceMapping;

use Laminas\Stdlib\ArrayObject;
use Laminas\Stdlib\ArrayUtils;

/**
 * Class Configuration
 * Handles the admin resource configuration management.
 *
 * Extends ArrayObject to manage configuration arrays.
 */
class Configuration extends ArrayObject
{
    /**
     * Finds a resource configuration by its name.
     *
     * @param string $name The name of the resource.
     *
     * @return array|null The found configuration or null if not found.
     */
    public function findResourceConfig(string $name): ?array
    {
        foreach ($this->getIterator() as $config) {
            if (isset($config['name']) && $config['name'] === $name) {
                return $config;
            }
            if (isset($config['spec']['name']) && $config['spec']['name'] === $name) {
                return $config;
            }
        }
        return null;
    }

    /**
     * Sets a resource configuration by its name.
     *
     * Updates the existing configuration if found.
     *
     * @param string $name   The name of the resource.
     * @param array  $config The configuration to set.
     *
     * @return void
     */
    public function setResourceConfig(string $name, array $config): void
    {
        foreach ($this->getIterator() as $key => $item) {
            if (isset($item['name']) && $item['name'] === $name) {
                $this->offsetSet($key, $config);
                break;
            }
            if (isset($item['spec']['name']) && $item['spec']['name'] === $name) {
                $this->offsetSet($key, $config);
                break;
            }
        }
    }

    /**
     * Merges additional configuration arrays into the current configuration.
     *
     * @param array ...$config Variable number of configuration arrays to merge.
     *
     * @return void
     */
    public function merge(array ...$config): void
    {
        $this->exchangeArray(array_merge($this->getArrayCopy(), ...$config));
    }

    /**
     * Collects and returns specifications from the current configurations.
     *
     * Sorts the configurations by priority before extracting specifications.
     *
     * @return array The list of collected specifications.
     */
    public function collectSpecification(): array
    {
        $out = [];
        $this->uasort(
            fn(array $a, array $b) => ($b['priority'] ?? 1) - ($a['priority'] ?? 1)
        );
        foreach ($this->getIterator() as $config) {
            if (isset($config['spec']) && (!isset($config['disabled']) || $config['disabled'] !== true)) {
                if (!isset($config['spec']['name'])) {
                    $config['spec']['name'] = $config['name'];
                }
                $out[] = $this->_fixSpec($config['spec']);
            }
        }

        return $out;
    }

    public function collectDashboard(): array
    {
        $out = [];
        $this->uasort(
            fn(array $a, array $b) => ($b['priority'] ?? 1) - ($a['priority'] ?? 1)
        );
        foreach ($this->getIterator() as $config) {
            if (isset($config['dashboard'])) {
                $out[] = [
                    'blocks' => $config['dashboard'],
                    'resource' => $config['spec']['name']?? $config['name']?? 'unknown'
                ];
            }
        }

        return $out;
    }

    protected function _fixSpec(array $spec): array
    {
        foreach (['list', 'show'] as $item) {
            if (isset($spec[$item]) && isset($spec[$item]['fields']) && ArrayUtils::isHashTable($spec[$item]['fields'])) {
                ksort($spec[$item]['fields']);
                $spec[$item]['fields'] = array_values($spec[$item]['fields']);
            }
        }

        foreach (['show', 'edit', 'create', 'form'] as $item) {
            if (isset($spec[$item]) && isset($spec[$item]['tabs']) && ArrayUtils::isHashTable($spec[$item]['tabs'])) {
                ksort($spec[$item]['tabs']);
                $spec[$item]['tabs'] = array_values($spec[$item]['tabs']);
            }
        }

        foreach (['form', 'edit', 'create'] as $item) {
            if (isset($spec[$item]) && isset($spec[$item]['inputs']) && ArrayUtils::isHashTable($spec[$item]['inputs'])) {
                ksort($spec[$item]['inputs']);
                $spec[$item]['inputs'] = array_values($spec[$item]['inputs']);
            }
        }

        return $spec;
    }
}
