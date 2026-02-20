<?php

namespace ExprAs\Doctrine\Hydrator;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\OneToMany;

trait CollectionAddRemoverTrait
{
    public function __call(string $name, array $arguments)
    {
        if (preg_match('~^(add|remove)([a-zA-Z]+)$~', $name, $match)) {
            [, $action, $collection] = $match;
            $lcCollection = lcfirst($collection);
            if (property_exists($this, $lcCollection) && $this->{$lcCollection} instanceof Collection) {
                $entities = current($arguments);
                if (is_iterable($entities)) {
                    foreach ($entities as $_entity) {
                        call_user_func_array([$this, $name], [$_entity]);
                    }
                    return;
                }
                if ($action === 'remove') {
                    $this->{$lcCollection}->removeElement($entities);
                } elseif ($action === 'add') {
                    if (!$this->{$lcCollection}->contains($entities)) {

                        $reflection = new \ReflectionProperty($this, $lcCollection);
                        $attr = $reflection->getAttributes(OneToMany::class);
                        if (count($attr)) {
                            $attr = current($attr);
                            $attrArgs = $attr->getArguments();
                            if (isset($attrArgs['mappedBy'])) {
                                $mappedByMethod = 'set' . ucfirst($attrArgs['mappedBy']);
                                if (method_exists($entities, $mappedByMethod)) {
                                    $entities->{$mappedByMethod}($this);
                                }
                            }

                        }
                        $this->{$lcCollection}->add($entities);
                    }
                }

                return;
            }
        }

        //throw new \BadMethodCallException(sprintf('Called to undefined method %s', $name));
    }
}
