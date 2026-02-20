<?php

namespace ExprAs\Rest\Hydrator\Strategy;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Laminas\Hydrator\Strategy\AbstractCollectionStrategy;
use Doctrine\Laminas\Hydrator\Strategy\AllowRemoveByValue;
use Doctrine\Persistence\Mapping\ClassMetadata;
use ExprAs\Doctrine\Hydrator\CollectionAddRemoverTrait;
use ExprAs\Rest\Hydrator\RestHydrator;
use LogicException;

class AssociationsStrategy extends AbstractCollectionStrategy
{
    /**
     * @var RestHydrator
     */
    protected RestHydrator $_hydrator;

    protected bool $_nullable = false;

    protected ?ClassMetadata $_collectionMetadata = null;

    protected bool $extractToOneObjects = false;

    protected bool $extractToManyObjects = false;

    /**
     * @return mixed
     */
    public function getCollectionMetadata(): ClassMetadata
    {
        return $this->_collectionMetadata;
    }

    /**
     * @param mixed $collectionMetadata
     */
    public function setCollectionMetadata(ClassMetadata $collectionMetadata): void
    {
        $this->_collectionMetadata = $collectionMetadata;
    }


    public function setHydrator(RestHydrator $hydrator): void
    {
        $this->_hydrator = $hydrator;
    }


    /**
     * @return RestHydrator
     */
    public function getHydrator(): RestHydrator
    {
        return $this->_hydrator;
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
        if ($value instanceof \Traversable) {
            $out = [];
            if ($this->isExtractToManyObjects()) {
                foreach ($value as $_entity) {
                    $out[] = $_o = $this->getHydrator()->extract($_entity);
                }
            } else {
                foreach ($value as $_entity) {
                    $out[] = current($this->getCollectionMetadata()->getIdentifierValues($_entity));
                }
            }

            return $out;
        } else {
            if ($this->isExtractToOneObjects()) {

                return $this->getHydrator()->extract($value);

            } else {
                return current($this->getCollectionMetadata()->getIdentifierValues($value));
            }

        }
    }

    /**
     * Converts the given value so that it can be hydrated by the hydrator.
     *
     * @param mixed      $value The original value.
     * @param null|array $data  The original data for context.
     *
     * @return mixed      Returns the value that should be hydrated.
     */
    public function hydrate($value, ?array $data)
    {
        if ($this->getClassMetadata()->isCollectionValuedAssociation($this->getCollectionName())) {
            return $this->_hydrate($value, $data);
        }
        if (!$value && $this->isNullable()) {
            return null;
        }
        return $value;
    }

    /**
     * Converts the given value so that it can be hydrated by the hydrator.
     *
     * @param mixed                        $value The original value.
     * @param array<array-key, mixed>|null $data  The original data for context.
     *
     * @return mixed Returns the value that should be hydrated.
     */
    public function _hydrate(mixed $value, ?array $data)
    {
        // AllowRemove strategy need "adder" and "remover"
        $adder = 'add' . $this->getInflector()->classify($this->getCollectionName());
        $remover = 'remove' . $this->getInflector()->classify($this->getCollectionName());
        $object = $this->getObject();

        if (!method_exists($object, $adder) || !method_exists($object, $remover)) {
            if (!in_array(CollectionAddRemoverTrait::class, class_uses($object))) {
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

        }

        $collection = $this->getCollectionFromObjectByValue();
        $collection = $collection->toArray();

        $toAdd = new ArrayCollection(array_udiff($value, $collection, $this->compareObjects(...)));
        $toRemove = new ArrayCollection(array_udiff($collection, $value, $this->compareObjects(...)));

        $object->$adder($toAdd);
        $object->$remover($toRemove);

        return $collection;
    }

    /**
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->_nullable;
    }

    public function setNullable(bool $nullable): void
    {
        $this->_nullable = $nullable;
    }

    /**
     * @return bool
     */
    public function isExtractToOneObjects(): bool
    {
        return $this->extractToOneObjects;
    }

    public function setExtractToOneObjects(bool $extractToOneObjects): void
    {
        $this->extractToOneObjects = $extractToOneObjects;
    }

    /**
     * @return bool
     */
    public function isExtractToManyObjects(): bool
    {
        return $this->extractToManyObjects;
    }

    public function setExtractToManyObjects(bool $extractToManyObjects): void
    {
        $this->extractToManyObjects = $extractToManyObjects;
    }


}
