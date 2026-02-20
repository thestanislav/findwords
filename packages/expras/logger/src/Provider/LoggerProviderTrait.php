<?php

namespace ExprAs\Logger\Provider;

use Psr\Log\LoggerInterface;

/**
 * Logger Provider Trait
 * 
 * Provides logger injection capability for any class.
 * Classes using this trait will automatically receive the expras_logger
 * when created via the service container (thanks to LoggerProviderInitializer).
 * 
 * Usage:
 * ```php
 * class MyService
 * {
 *     use LoggerProviderTrait;
 *     
 *     public function doSomething()
 *     {
 *         $this->logger->info('Something happened');
 *     }
 * }
 * ```
 */
trait LoggerProviderTrait
{
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Set the logger instance
     * 
     * @param LoggerInterface $logger
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}

