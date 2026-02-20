<?php

namespace ExprAs\Doctrine\Behavior\Singleable;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use ExprAs\Doctrine\Behavior\Singleable\Mapping\Annotation\Singleable;
use Gedmo\Mapping\Driver\AttributeReader;
use Gedmo\Mapping\MappedEventSubscriber;

/**
 * SingleableListener
 * 
 * Doctrine event subscriber that ensures only one entity in a group 
 * can have a specific field value (e.g., only one default address per user).
 * 
 * Works across all database platforms (MySQL, PostgreSQL, SQLite, etc.)
 * by using platform-agnostic DQL queries.
 */
class SingleableListener extends MappedEventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata, 
            Events::prePersist, Events::preUpdate, 
            Events::preRemove
        ];
    }

    protected function getNamespace(): string
    {
        return __NAMESPACE__;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        if (!in_array(SingleableInterface::class, $eventArgs->getClassMetadata()->getReflectionClass()->getInterfaceNames())) {
            return;
        }

        $this->setAnnotationReader(new AttributeReader());
        $this->loadMetadataForObjectClass($eventArgs->getObjectManager(), $eventArgs->getClassMetadata());
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        if ($args->getObject() instanceof SingleableInterface) {
            $this->cancelOtherEntities($args->getObjectManager(), $args->getObject());
            $this->ensureFirstEntityHasValue($args->getObjectManager(), $args->getObject());
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        if ($args->getObject() instanceof SingleableInterface) {
            $this->cancelOtherEntities($args->getObjectManager(), $args->getObject());
            $this->ensureFirstEntityHasValue($args->getObjectManager(), $args->getObject());
        }
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        if ($args->getObject() instanceof SingleableInterface) {
            $this->ensureFirstEntityHasValue($args->getObjectManager(), $args->getObject());
        }
    }

    /**
     * Resets the field value on all other entities in the group when the current entity 
     * gets the exclusive value.
     * 
     * @param EntityManager $manager
     * @param SingleableInterface $object
     */
    protected function cancelOtherEntities(EntityManager $manager, SingleableInterface $object): void
    {
        $entityName = $object::class;
        if (!($config = $this->getConfiguration($manager, $entityName))) {
            return;
        }

        if (!isset($config['singleables']) || !count($config['singleables'])) {
            return;
        }

        $metadata = $manager->getClassMetadata($entityName);
        $ids = $metadata->getIdentifierValues($object);

        // Refresh entity if it exists in database to get current state
        if ($ids) {
            $manager->refresh($object);
        }

        foreach ($config['singleables'] as $field => $singleable) {
            /** @var Singleable $singleable */
            if (!$singleable->isCancelEscorted()) {
                continue;
            }

            // Only process if current entity has the exclusive value
            $currentValue = $metadata->getFieldValue($object, $field);
            if ($currentValue !== $singleable->getValue()) {
                continue;
            }

            $parameters = [
                $field => $singleable->getCancelValue()
            ];

            // Build DQL UPDATE query (platform-agnostic)
            $dql = sprintf('UPDATE %s e SET e.%s = :%s WHERE 1=1', $config['useObjectClass'], $field, $field);

            // Apply static filters
            if (count($singleable->getFilters())) {
                foreach ($singleable->getFilters() as $filterField => $value) {
                    $dql .= ' AND e.' . $filterField . ' = :' . $filterField;
                    $parameters[$filterField] = $value;
                }
            }

            // Apply group filters
            if (count($singleable->getGroup())) {
                foreach ($singleable->getGroup() as $groupField) {
                    $dql .= ' AND e.' . $groupField . ' = :' . $groupField;
                    $parameters[$groupField] = $metadata->getFieldValue($object, $groupField);
                }
            }

            // Exclude current entity (if it has an ID)
            if (count($ids)) {
                $dql .= ' AND (';
                $first = true;
                foreach ($ids as $idField => $idValue) {
                    if (!$first) {
                        $dql .= ' OR ';
                    }
                    $dql .= 'e.' . $idField . ' != :' . $idField;
                    $parameters[$idField] = $idValue;
                    $first = false;
                }
                $dql .= ')';
            }

            $query = $manager->createQuery($dql);
            $query->setParameters($parameters);
            $query->execute();
        }
    }

    /**
     * Ensures at least one entity in the group has the exclusive value.
     * If no entity has the value, sets it on the first available entity.
     * 
     * @param EntityManager $manager
     * @param SingleableInterface $object
     */
    protected function ensureFirstEntityHasValue(EntityManager $manager, SingleableInterface $object): void
    {
        $entityName = $object::class;
        if (!($config = $this->getConfiguration($manager, $entityName))) {
            return;
        }

        if (!isset($config['singleables']) || !count($config['singleables'])) {
            return;
        }

        $metadata = $manager->getClassMetadata($entityName);

        // Refresh entity if it exists in database
        if ($metadata->getIdentifierValues($object)) {
            $manager->refresh($object);
        }

        foreach ($config['singleables'] as $field => $singleable) {
            /** @var Singleable $singleable */
            
            // Skip if current entity already has the exclusive value
            if ($metadata->getFieldValue($object, $field) === $singleable->getValue()) {
                continue;
            }

            $parameters = [
                $field => $singleable->getValue()
            ];

            // Build DQL SELECT COUNT query (platform-agnostic)
            $dql = sprintf('SELECT COUNT(e) FROM %s e WHERE e.%s = :%s', $config['useObjectClass'], $field, $field);

            // Apply static filters (from both filters and ensureFilters)
            $allFilters = array_merge($singleable->getFilters(), $singleable->getEnsureFilters());
            if (count($allFilters)) {
                foreach ($allFilters as $filterField => $value) {
                    $dql .= ' AND e.' . $filterField . ' = :' . $filterField;
                    $parameters[$filterField] = $value;
                }
            }

            // Apply group filters
            if (count($singleable->getGroup())) {
                foreach ($singleable->getGroup() as $groupField) {
                    $dql .= ' AND e.' . $groupField . ' = :' . $groupField;
                    $parameters[$groupField] = $metadata->getFieldValue($object, $groupField);
                }
            }

            $query = $manager->createQuery($dql);
            $query->setParameters($parameters);
            $count = $query->getSingleScalarResult();

            // If no entity has the value, set it on the current entity
            if ($count === 0) {
                $metadata->setFieldValue($object, $field, $singleable->getValue());
            }
        }
    }
}

