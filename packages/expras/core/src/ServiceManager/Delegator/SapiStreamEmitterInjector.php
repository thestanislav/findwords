<?php

namespace ExprAs\Core\ServiceManager\Delegator;

use Laminas\HttpHandlerRunner\Emitter\EmitterStack;
use Laminas\HttpHandlerRunner\Emitter\SapiStreamEmitter;
use Psr\Container\ContainerInterface;

class SapiStreamEmitterInjector
{

    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): EmitterStack
    {
        $emitterStack = $callback();
        $emitterStack->push(new SapiStreamEmitter());

        return $emitterStack;
    }
}