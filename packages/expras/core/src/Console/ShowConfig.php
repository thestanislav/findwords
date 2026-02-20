<?php

namespace ExprAs\Core\Console;

use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use Laminas\Config\Writer\Ini;
use Laminas\Config\Writer\JavaProperties;
use Laminas\Config\Writer\Json;
use Laminas\Config\Writer\PhpArray;
use Laminas\Config\Writer\Xml;
use Laminas\Config\Writer\Yaml;
use Laminas\Config\Config;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'expras:show-config', description: 'Shows the current configuration')]
class ShowConfig extends Command
{

    use ServiceContainerAwareTrait;
    
    protected function configure()
    {
        $this->addOption(
            'writer',
            'w',
            InputOption::VALUE_REQUIRED,
            'Output format: ini, javaproperties, json, phparray, xml, yaml',
            'phparray'
        );
        
        $this->addOption(
            'filter',
            'f',
            InputOption::VALUE_REQUIRED,
            'Filter configuration by key path (e.g., "nutgram.token" or "database.params")',
            null
        );
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rawConfig = $this->getContainer()->get('config');
        
        // Apply filter if specified
        $filter = $input->getOption('filter');
        if ($filter) {
            $rawConfig = $this->filterByPath($rawConfig, $filter);
        }
        
        // Filter out Closures and replace with placeholder
        $filteredConfig = $this->filterClosures($rawConfig);
        
        // Ensure we have an array for Config constructor
        if (!is_array($filteredConfig)) {
            $filteredConfig = ['value' => $filteredConfig];
        }
        
        $config = new Config($filteredConfig, false);

        // Get writer type
        $writerType = strtolower($input->getOption('writer'));
        $writer = $this->createWriter($writerType);
        
        $output->write($writer->toString($config));

        return 0;
    }
    
    /**
     * Create writer instance based on type
     */
    private function createWriter(string $writerType)
    {
        switch ($writerType) {
            case 'ini':
                $writer = new Ini();
                $writer->setRenderWithoutSectionsFlags(true);
                return $writer;
                
            case 'javaproperties':
                return new JavaProperties();
                
            case 'json':
                return new Json();
                
            case 'phparray':
                return new PhpArray();
                
            case 'xml':
                return new Xml();
                
            case 'yaml':
                return new Yaml();
                
            default:
                throw new \InvalidArgumentException(
                    "Unsupported writer type: {$writerType}. " .
                    "Supported types: ini, javaproperties, json, phparray, xml, yaml"
                );
        }
    }
    
    /**
     * Filter configuration by dot-notation path
     */
    private function filterByPath(array $config, string $path)
    {
        $keys = explode('.', $path);
        $current = $config;
        
        foreach ($keys as $key) {
            if (!is_array($current) || !array_key_exists($key, $current)) {
                throw new \InvalidArgumentException("Configuration path '{$path}' not found");
            }
            $current = $current[$key];
        }
        
        return $current;
    }
    
    /**
     * Recursively filter out non-serializable values from config array
     */
    private function filterClosures($data)
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                if ($value instanceof \Closure) {
                    $result[$key] = '[CLOSURE]';
                } elseif (is_object($value)) {
                    $result[$key] = '[OBJECT: ' . get_class($value) . ']';
                } elseif (is_resource($value)) {
                    $result[$key] = '[RESOURCE]';
                } elseif (is_array($value)) {
                    $result[$key] = $this->filterClosures($value);
                } else {
                    $result[$key] = $this->filterClosures($value);
                }
            }
            return $result;
        }
        
        return $data;
    }
}