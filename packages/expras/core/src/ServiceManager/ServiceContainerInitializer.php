<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 2/9/2018
 * Time: 22:01
 */

namespace ExprAs\Core\ServiceManager;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Initializer\InitializerInterface;

class ServiceContainerInitializer extends AbstractTraitInitializer
{
    public function __invoke(ContainerInterface $container, $instance)
    {

        if (!is_object($instance)) {
            return;
        }

        $ref = new \ReflectionObject($instance);
        if (in_array(ServiceContainerAwareTrait::class, $this->getTraitNames($ref))) {
            $instance->setContainer($container);
            return;
        }


        if (!$ref->hasMethod('setContainer')) {
            return;
        }

        $method = $ref->getMethod('setContainer');
        if (!count($method->getParameters())) {
            return;
        }
        $parameter = $method->getParameters()[0];
        if (!$parameter->getClass()) {
            return;
        }
        if ($parameter->getClass()->name == ContainerInterface::class) {
            $instance->setContainer($container);
        }
    }
}
