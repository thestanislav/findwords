<?php

namespace ExprAs\Logger\Provider;

use Laminas\ServiceManager\Initializer\InitializerInterface;
use Psr\Container\ContainerInterface;

/**
 * Logger Provider Initializer
 * 
 * Automatically injects expras_logger into any service that uses LoggerProviderTrait.
 * 
 * This initializer runs after a service is created and checks if it uses LoggerProviderTrait.
 * If it does, it calls setLogger() with the expras_logger service.
 * 
 * Usage:
 * ```php
 * // In config:
 * 'dependencies' => [
 *     'initializers' => [
 *         LoggerProviderInitializer::class,
 *     ],
 * ],
 * 
 * // In your class:
 * class MyService
 * {
 *     use LoggerProviderTrait;
 *     
 *     public function doSomething()
 *     {
 *         $this->logger->info('Something happened');
 *     }
 * }
 * 
 * // The logger is automatically injected when the service is created:
 * $service = $container->get(MyService::class);
 * $service->doSomething(); // Logger is available
 * ```
 * 
 * Benefits of Initializer approach:
 * - Simpler implementation than abstract factories
 * - Works with ANY factory (ConfigAbstractFactory, custom factories, InvokableFactory, etc.)
 * - No need to handle service creation logic
 * - Just checks trait and injects logger after service is created
 * - Runs on EVERY service creation, so no configuration needed
 */
class LoggerProviderInitializer implements InitializerInterface
{
    /**
     * Initialize the instance
     * 
     * Checks if instance uses LoggerProviderTrait and injects logger if so.
     * 
     * @param ContainerInterface $container
     * @param object $instance
     * @return void
     */
    public function __invoke(ContainerInterface $container, $instance): void
    {
        // Check if instance uses LoggerProviderTrait
        if (!$this->usesLoggerProviderTrait($instance)) {
            return;
        }
        
        // Check if instance has setLogger method
        if (!method_exists($instance, 'setLogger')) {
            return;
        }
        
        // Inject the logger
        $logger = $container->get('expras_logger');
        $instance->setLogger($logger);
    }
    
    /**
     * Check if instance uses LoggerProviderTrait
     * 
     * @param object $instance
     * @return bool
     */
    private function usesLoggerProviderTrait(object $instance): bool
    {
        $class = get_class($instance);
        $traits = class_uses($class);
        
        if ($traits === false) {
            return false;
        }
        
        // Check direct trait usage
        if (in_array(LoggerProviderTrait::class, $traits, true)) {
            return true;
        }
        
        // Check parent classes for trait usage
        $parentClass = get_parent_class($class);
        if ($parentClass !== false) {
            return $this->usesLoggerProviderTraitInClass($parentClass);
        }
        
        return false;
    }
    
    /**
     * Check if class uses LoggerProviderTrait (recursive for parent classes)
     * 
     * @param string $className
     * @return bool
     */
    private function usesLoggerProviderTraitInClass(string $className): bool
    {
        $traits = class_uses($className);
        
        if ($traits === false) {
            return false;
        }
        
        // Check direct trait usage
        if (in_array(LoggerProviderTrait::class, $traits, true)) {
            return true;
        }
        
        // Check parent classes
        $parentClass = get_parent_class($className);
        if ($parentClass !== false) {
            return $this->usesLoggerProviderTraitInClass($parentClass);
        }
        
        return false;
    }
}

