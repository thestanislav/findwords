<?php

namespace ExprAs\Logger\Factory;

use ExprAs\Logger\LoggerProviderTrait;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;

/**
 * Logger Provider Abstract Factory
 * 
 * Automatically injects expras_logger into any service that uses LoggerProviderTrait.
 * 
 * This factory checks if a requested service class:
 * 1. Exists as a class
 * 2. Uses the LoggerProviderTrait
 * 
 * If both conditions are met, it:
 * 1. Creates an instance of the class (must have no-arg constructor or all optional params)
 * 2. Calls setLogger() with the expras_logger service
 * 
 * Usage:
 * ```php
 * // In config:
 * 'dependencies' => [
 *     'abstract_factories' => [
 *         LoggerProviderAbstractFactory::class,
 *     ],
 * ],
 * 
 * // In your class:
 * class MyService
 * {
 *     use LoggerProviderTrait;
 *     
 *     public function __construct(SomeDependency $dep)
 *     {
 *         // Constructor can have dependencies
 *     }
 * }
 * 
 * // Configure dependencies via ConfigAbstractFactory:
 * use Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory;
 * 
 * return [
 *     ConfigAbstractFactory::class => [
 *         MyService::class => [
 *             SomeDependency::class,
 *         ],
 *     ],
 * ];
 * ```
 * 
 * Note: This factory works best with services that have explicit factories
 * or are configured via ConfigAbstractFactory. For services with no-arg constructors,
 * it can create them directly.
 */
class LoggerProviderAbstractFactory implements AbstractFactoryInterface
{
    /**
     * Can the factory create the requested service?
     * 
     * Checks if:
     * 1. The requested name is a class that exists
     * 2. The class uses LoggerProviderTrait
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName): bool
    {
        // Must be a class
        if (!class_exists($requestedName)) {
            return false;
        }

        // Check if class uses LoggerProviderTrait
        return $this->usesLoggerProviderTrait($requestedName);
    }

    /**
     * Create the service and inject the logger
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): object
    {
        // Try to get the service from another factory first
        // This allows ConfigAbstractFactory or other factories to handle construction
        $service = $this->createServiceInstance($container, $requestedName, $options);
        
        // Inject the logger
        if (method_exists($service, 'setLogger')) {
            $logger = $container->get('expras_logger');
            $service->setLogger($logger);
        }

        return $service;
    }

    /**
     * Create the service instance
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object
     * @throws \Exception
     */
    private function createServiceInstance(ContainerInterface $container, string $requestedName, ?array $options = null): object
    {
        // Check if ConfigAbstractFactory can handle this
        if ($this->hasConfigAbstractFactoryConfig($container, $requestedName)) {
            $configFactory = new \Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory();
            if ($configFactory->canCreate($container, $requestedName)) {
                return $configFactory($container, $requestedName, $options);
            }
        }

        // Fallback: Try to instantiate directly (requires no-arg constructor or all optional params)
        $reflection = new ReflectionClass($requestedName);
        
        if (!$reflection->isInstantiable()) {
            throw new \Exception(sprintf(
                'Class %s is not instantiable. Please provide a factory or ConfigAbstractFactory configuration.',
                $requestedName
            ));
        }

        $constructor = $reflection->getConstructor();
        
        // If no constructor, just instantiate
        if ($constructor === null) {
            return new $requestedName();
        }

        // Check if all constructor params are optional
        $params = $constructor->getParameters();
        $allOptional = true;
        foreach ($params as $param) {
            if (!$param->isOptional() && !$param->allowsNull()) {
                $allOptional = false;
                break;
            }
        }

        if ($allOptional) {
            return new $requestedName();
        }

        throw new \Exception(sprintf(
            'Cannot automatically create %s. Constructor has required parameters. ' .
            'Please configure it via ConfigAbstractFactory or provide a custom factory.',
            $requestedName
        ));
    }

    /**
     * Check if class uses LoggerProviderTrait
     * 
     * @param string $className
     * @return bool
     */
    private function usesLoggerProviderTrait(string $className): bool
    {
        $traits = class_uses($className);
        
        if ($traits === false) {
            return false;
        }

        if (count($traits) === 0) {
            return false;
        }

        // Check direct trait usage
        if (in_array(LoggerProviderTrait::class, $traits)) {
            return true;
        }

        // Check parent classes for trait usage
        $parentClass = get_parent_class($className);
        if ($parentClass !== false) {
            return $this->usesLoggerProviderTrait($parentClass);
        }

        return false;
    }

    /**
     * Check if ConfigAbstractFactory has configuration for this service
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    private function hasConfigAbstractFactoryConfig(ContainerInterface $container, string $requestedName): bool
    {
        $config = $container->get('config');
        $factoryConfig = $config[\Laminas\ServiceManager\AbstractFactory\ConfigAbstractFactory::class] ?? [];
        
        return isset($factoryConfig[$requestedName]);
    }
}

