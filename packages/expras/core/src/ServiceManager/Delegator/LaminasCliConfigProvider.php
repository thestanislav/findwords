<?php

namespace ExprAs\Core\ServiceManager\Delegator;

use Mezzio\Application;
use Psr\Container\ContainerInterface;

class LaminasCliConfigProvider
{
    /**
     * @param  $serviceName
     * @return Application
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        /**
 * @var $app Application 
*/
        $app = $callback();

        return $app;
    }
}
