<?php

namespace ExprAs\Doctrine\Behavior\Ratable;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Proxy\Proxy;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\ObjectManager;
use ExprAs\Doctrine\Behavior\Ratable\Mapping\Annotation\Ratable;
use Gedmo\Mapping\Driver\AttributeReader;
use Gedmo\Mapping\MappedEventSubscriber;
use Doctrine\ORM\Events;
use RuntimeException;

/**
 * RatableListener automatically assigns sequential ratings/rankings to entities
 * based on sorting criteria. Uses database-agnostic DQL for portability.
 */
class RatableListener extends MappedEventSubscriber
{
    /**
     * Queue of rating operations to perform after flush.
     * Format: [queueKey => ['entityName' => ..., 'field' => ..., 'annot' => ..., 'groupValue' => ...]]
     */
    protected array $_queue = [];

    /**
     * Track entity IDs scheduled for deletion to skip them in rating calculations.
     */
    protected array $_deletionQueue = [];

    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
            Events::prePersist,
            Events::preRemove,
            Events::preUpdate,
            Events::preFlush,
            Events::postFlush,
        ];
    }

    /**
     * Extract entity class name, handling Doctrine proxies correctly.
     */
    protected function getEntityName(object $entity): string
    {
        return $entity instanceof Proxy ? get_parent_class($entity) : $entity::class;
    }

    /**
     * Queue all ratable fields of an entity for recalculation.
     */
    protected function queueMatched(object $entity, ObjectManager $objectManager): void
    {
        $entityName = $this->getEntityName($entity);
        
        if (!($config = $this->getConfiguration($objectManager, $entityName))) {
            return;
        }

        $metaData = $objectManager->getClassMetadata($entityName);

        foreach ($config as $_field => $_config) {
            if ($_field === 'useObjectClass') {
                continue;
            }

            /** @var Ratable $_config */
            
            // Determine group value for this entity if grouping is enabled
            $groupValue = null;
            if ($groupField = $_config->getGroup()) {
                $groupValue = $metaData->getFieldValue($entity, $groupField);
                
                // Extract ID if group field is an association
                if ($groupValue && $metaData->hasAssociation($groupField)) {
                    $groupMetaData = $objectManager->getClassMetadata($groupValue::class);
                    $identifierValues = $groupMetaData->getIdentifierValues($groupValue);
                    $groupValue = $identifierValues['id'] ?? null;
                }
            }

            // Queue key includes entity, field, and group to only re-rate affected groups
            $queueKey = $this->generateQueueKey($entityName, $_field, $groupValue);
            
            $this->_queue[$queueKey] = [
                'entityName' => $entityName,
                'field'      => $_field,
                'annot'      => $_config,
                'groupValue' => $groupValue,
            ];
        }
    }

    /**
     * Generate unique queue key for entity+field+group combination.
     */
    protected function generateQueueKey(string $entityName, string $field, mixed $groupValue): string
    {
        $key = strtolower($entityName . '_' . $field);
        if ($groupValue !== null) {
            $key .= '_' . $groupValue;
        }
        return $key;
    }

    protected function getNamespace(): string
    {
        return __NAMESPACE__;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $this->setAnnotationReader(new AttributeReader());
        $this->loadMetadataForObjectClass($eventArgs->getObjectManager(), $eventArgs->getClassMetadata());
    }

    public function prePersist(LifecycleEventArgs $eventArgs): void
    {
        $this->queueMatched($eventArgs->getObject(), $eventArgs->getObjectManager());
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getObject();
        $entityName = $this->getEntityName($entity);

        if (!($config = $this->getConfiguration($eventArgs->getObjectManager(), $entityName))) {
            return;
        }

        $metaData = $eventArgs->getObjectManager()->getClassMetadata($entityName);
        $changeSet = $eventArgs->getEntityChangeSet();

        foreach ($config as $_field => $_config) {
            if ($_field === 'useObjectClass') {
                continue;
            }

            /** @var Ratable $_config */

            // Determine which fields affect this ratable field
            $dependingFields = array_keys($_config->getSort());
            
            // Add group field if present
            if ($groupField = $_config->getGroup()) {
                $dependingFields[] = $groupField;
            }

            // Add criteria fields
            foreach ($_config->getCriteria() as $criterion) {
                if (is_array($criterion) && isset($criterion[0])) {
                    $dependingFields[] = $criterion[0];
                }
            }

            // Only queue if any dependent field changed
            if (count(array_intersect(array_keys($changeSet), $dependingFields)) > 0) {
                // Get group value for queue key
                $groupValue = null;
                if ($groupField = $_config->getGroup()) {
                    $groupValue = $metaData->getFieldValue($entity, $groupField);
                    if ($groupValue && $metaData->hasAssociation($groupField)) {
                        $groupMetaData = $eventArgs->getObjectManager()->getClassMetadata($groupValue::class);
                        $identifierValues = $groupMetaData->getIdentifierValues($groupValue);
                        $groupValue = $identifierValues['id'] ?? null;
                    }
                }

                $queueKey = $this->generateQueueKey($entityName, $_field, $groupValue);
                
                $this->_queue[$queueKey] = [
                    'entityName' => $entityName,
                    'field'      => $_field,
                    'annot'      => $_config,
                    'groupValue' => $groupValue,
                ];
            }
        }
    }

    public function preFlush(PreFlushEventArgs $eventArgs): void
    {
        $em = $eventArgs->getObjectManager();
        $this->_deletionQueue = array_keys($em->getUnitOfWork()->getScheduledEntityDeletions());
    }

    public function preRemove(LifecycleEventArgs $eventArgs): void
    {
        $this->queueMatched($eventArgs->getObject(), $eventArgs->getObjectManager());
    }

    /**
     * @throws OptimisticLockException
     * @throws NonUniqueResultException
     * @throws ORMException
     */
    public function postFlush(PostFlushEventArgs $eventArgs): void
    {
        $this->execute($eventArgs);
    }

    /**
     * Execute all queued rating calculations using database-agnostic DQL.
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function execute(PostFlushEventArgs $eventArgs): void
    {
        if (empty($this->_queue)) {
            return;
        }

        $em = $eventArgs->getEntityManager();
        $updateCount = 0;

        // Sort queue by priority (higher priority first)
        uasort($this->_queue, function($a, $b) {
            return $b['annot']->getPriority() - $a['annot']->getPriority();
        });

        foreach ($this->_queue as $queueItem) {
            ['entityName' => $entityName, 'field' => $field, 'annot' => $annot, 'groupValue' => $groupValue] = $queueItem;
            
            /** @var Ratable $annot */
            
            try {
                $metaData = $em->getClassMetadata($entityName);

                // Validate field exists and is appropriate type
                if (!$metaData->hasField($field)) {
                    throw new RuntimeException("Ratable field '{$field}' does not exist in entity '{$entityName}'");
                }

                $fieldType = $metaData->getTypeOfField($field);
                if (!in_array($fieldType, ['integer', 'smallint', 'bigint'])) {
                    throw new RuntimeException("Ratable field '{$field}' must be an integer type, got '{$fieldType}'");
                }

                // Build query to fetch entities in rating order
                $qb = $em->createQueryBuilder();
                $qb->select('e')
                   ->from($entityName, 'e');

                // Apply criteria filters
                if ($criteria = $annot->getCriteria()) {
                    $this->applyCriteria($qb, $criteria, $metaData);
                }

                // Apply group filter if specified
                if ($groupField = $annot->getGroup()) {
                    if (!$metaData->hasField($groupField) && !$metaData->hasAssociation($groupField)) {
                        throw new RuntimeException("Group field '{$groupField}' does not exist in entity '{$entityName}'");
                    }
                    
                    $qb->andWhere($qb->expr()->eq('e.' . $groupField, ':groupValue'))
                       ->setParameter('groupValue', $groupValue);
                }

                // Apply sorting
                foreach ($annot->getSort() as $sortField => $sortDirection) {
                    if (!$metaData->hasField($sortField) && !$metaData->hasAssociation($sortField)) {
                        throw new RuntimeException("Sort field '{$sortField}' does not exist in entity '{$entityName}'");
                    }
                    $qb->addOrderBy('e.' . $sortField, strtoupper($sortDirection));
                }

                // Fetch entities to rate
                $entities = $qb->getQuery()->getResult();

                // Assign ratings sequentially
                $rating = $annot->getStart();
                $setterMethod = 'set' . ucfirst($field);

                if (!method_exists($entityName, $setterMethod)) {
                    throw new RuntimeException("Setter method '{$setterMethod}' does not exist in entity '{$entityName}'");
                }

                foreach ($entities as $entity) {
                    $entityId = spl_object_id($entity);
                    
                    // Skip entities scheduled for deletion
                    if (in_array($entityId, $this->_deletionQueue)) {
                        continue;
                    }

                    $entity->$setterMethod($rating);
                    $em->persist($entity);
                    $rating++;
                    $updateCount++;
                }

                // Handle entities not matching criteria (assign default value if specified)
                if ($annot->getDefault() !== null && !empty($criteria)) {
                    $this->applyDefaultRating($em, $entityName, $field, $annot, $groupValue, $entities);
                }

            } catch (\Exception $e) {
                // Log error but continue processing other queue items
                error_log("Ratable behavior error for {$entityName}.{$field}: " . $e->getMessage());
                continue;
            }
        }

        // Clear queue and flush if any updates were made
        $this->_queue = [];
        $this->_deletionQueue = [];
        
        if ($updateCount > 0) {
            $em->flush();
        }
    }

    /**
     * Apply criteria filters to query builder.
     */
    protected function applyCriteria($qb, array $criteria, $metaData): void
    {
        $expr = new Expr();
        
        foreach ($criteria as $criterion) {
            if (!is_array($criterion) || count($criterion) < 2) {
                continue;
            }

            $field = $criterion[0];
            $operator = $criterion[1];
            $args = array_slice($criterion, 2);

            // Validate field exists
            if (!$metaData->hasField($field) && !$metaData->hasAssociation($field)) {
                throw new RuntimeException("Criteria field '{$field}' does not exist in entity");
            }

            // Validate operator is a valid Expr method
            if (!method_exists($expr, $operator)) {
                throw new RuntimeException("Invalid criteria operator '{$operator}'");
            }

            // Build criterion expression
            $criterionExpr = $expr->{$operator}('e.' . $field, ...$args);
            $qb->andWhere($criterionExpr);
        }
    }

    /**
     * Apply default rating to entities not matching criteria.
     */
    protected function applyDefaultRating($em, string $entityName, string $field, Ratable $annot, $groupValue, array $ratedEntities): void
    {
        // Build query for entities NOT matching criteria
        $qb = $em->createQueryBuilder();
        $qb->select('e')
           ->from($entityName, 'e');

        // Exclude already rated entities
        if (!empty($ratedEntities)) {
            $ratedIds = array_map(function($entity) use ($em, $entityName) {
                $metaData = $em->getClassMetadata($entityName);
                $ids = $metaData->getIdentifierValues($entity);
                return $ids['id'] ?? null;
            }, $ratedEntities);
            
            $ratedIds = array_filter($ratedIds);
            if (!empty($ratedIds)) {
                $qb->andWhere($qb->expr()->notIn('e.id', ':ratedIds'))
                   ->setParameter('ratedIds', $ratedIds);
            }
        }

        // Apply group filter if specified
        if ($groupField = $annot->getGroup()) {
            $qb->andWhere($qb->expr()->eq('e.' . $groupField, ':groupValue'))
               ->setParameter('groupValue', $groupValue);
        }

        $unratedEntities = $qb->getQuery()->getResult();
        $setterMethod = 'set' . ucfirst($field);
        $defaultValue = $annot->getDefault();

        foreach ($unratedEntities as $entity) {
            $entity->$setterMethod($defaultValue);
            $em->persist($entity);
        }
    }
}
