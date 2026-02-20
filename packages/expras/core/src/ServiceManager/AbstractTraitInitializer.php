<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 2/9/2018
 * Time: 22:01
 */

namespace ExprAs\Core\ServiceManager;

use Bot\Interface\UserAwareTrait;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Initializer\InitializerInterface;

abstract class AbstractTraitInitializer implements InitializerInterface
{
    abstract public function __invoke(ContainerInterface $container, $instance);

    public function getTraitNames(\ReflectionClass $reflectionClass): array
    {

        $traits = [];
        $class = $reflectionClass->getName();
        do {
            $traits = array_merge(class_uses($class), $traits);
        } while ($class = get_parent_class($class));

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait), $traits);
        }

        return array_unique($traits);

        /*
        $traitsNames = $reflectionClass->getTraitNames();
        if ($reflectionClass->getParentClass() != false) {
            $traitsNames = array_merge(
                $traitsNames,
                call_user_func_array([$this, __METHOD__], [$reflectionClass->getParentClass()])
            );
        }

        return $traitsNames;
        */
    }

    public function hasTrait(object $instance, $traitName): bool
    {
        $traits = $this->getTraitNames(new \ReflectionClass($instance::class));
        return in_array(UserAwareTrait::class, $traits);
    }
}
