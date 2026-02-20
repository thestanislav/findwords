<?php

namespace ExprAs\Admin\Hydrator\Strategy;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Laminas\Hydrator\Strategy\AbstractCollectionStrategy;
use ExprAs\Rest\Hydrator\RestHydrator;
use Laminas\Hydrator\Exception\LogicException;

class AssociationsStrategy extends AbstractCollectionStrategy
{
    protected bool $extractRecursive = false;

    protected RestHydrator $hydrator;

    /**
     * @return RestHydrator
     */
    public function getHydrator(): RestHydrator
    {
        return $this->hydrator;
    }

    public function setHydrator(RestHydrator $hydrator): void
    {
        $this->hydrator = $hydrator;
    }



    /**
     * @return bool
     */
    public function isExtractRecursive(): bool
    {
        return $this->extractRecursive;
    }

    public function setExtractRecursive(bool $extractRecursive): void
    {
        $this->extractRecursive = $extractRecursive;
    }



    /**
     * Converts the given value so that it can be extracted by the hydrator.
     *
     * @param mixed       $value  The original value.
     * @param null|object $object (optional) The original object for context.
     *
     * @return mixed       Returns the value that should be extracted.
     */
    #[\Override]
    public function extract($value, ?object $object = null)
    {
        if (is_null($value)) {
            return $value;
        }

        $hydrator = new RestHydrator($this->getHydrator()->getObjectManager());

        if ($value instanceof \Traversable) {
            $out = [];
            if ($this->isExtractRecursive()) {
                foreach ($value as $_entity) {
                    $out[] = ($hydrator)->extract($_entity);

                }
            } else {
                foreach ($value as $_entity) {
                    if (!isset($mt)) {
                        $mt = $hydrator->getObjectManager()->getClassMetadata($_entity::class);
                    }
                    $out[] = $mt->getIdentifierValues($_entity);
                }
            }

            return $out;
        } else {
            if ($this->isExtractRecursive()) {
                return $hydrator->extract($value);
            } else {
                $mt = $hydrator->getObjectManager()->getClassMetadata($value::class);
                return $mt->getIdentifierValues($value);
            }

        }
    }




    /**
     * Converts the given value so that it can be hydrated by the hydrator.
     *
     * @param mixed                        $value The original value.
     * @param array<array-key, mixed>|null $data  The original data for context.
     *
     * @return mixed Returns the value that should be hydrated.
     */
    public function hydrate($value, ?array $data)
    {

        if (!$value instanceof Collection) {
            return $value;
        }

        // AllowRemove strategy need "adder" and "remover"
        $adder   = 'add' . $this->getInflector()->classify($this->getCollectionName());
        $remover = 'remove' . $this->getInflector()->classify($this->getCollectionName());
        $object  = $this->getObject();

        if (! method_exists($object, $adder) || ! method_exists($object, $remover)) {
            throw new LogicException(
                sprintf(
                    'AllowRemove strategy for DoctrineModule hydrator requires both %s and %s to be defined in %s
                     entity domain code, but one or both seem to be missing',
                    $adder,
                    $remover,
                    $object::class
                )
            );
        }

        $collection = $this->getCollectionFromObjectByValue();
        $collection = $collection->toArray();

        $toAdd    = new ArrayCollection(array_udiff($value, $collection, $this->compareObjects(...)));
        $toRemove = new ArrayCollection(array_udiff($collection, $value, $this->compareObjects(...)));

        $object->$adder($toAdd);
        $object->$remover($toRemove);

        return $collection;
    }
}
