<?php

namespace ExprAs\Doctrine\Behavior\Aggregatable;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Proxy\Proxy;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use ExprAs\Doctrine\Behavior\Aggregatable\Mapping\Annotation\Aggregatable;
use ExprAs\Doctrine\Behavior\Ratable\Mapping\Annotation\Ratable;
use Gedmo\Mapping\Driver\AttributeReader;
use Gedmo\Mapping\MappedEventSubscriber;
use Doctrine\ORM\Events;

/**
 * AggregatableListener - Doctrine Event Listener for Automatic Field Aggregation
 * 
 * This listener automatically calculates and updates aggregate fields (sums, counts, averages, etc.)
 * on entity properties based on related collections. It uses Doctrine events to detect changes
 * in related entities and recalculates the aggregate values accordingly.
 * 
 * Key Features:
 * - Automatic aggregation of collection data (sum, count, avg, min, max)
 * - Support for complex filtering using Doctrine Criteria
 * - Priority-based execution order for multiple aggregatable fields
 * - Handles entity creation, updates, and deletions
 * - Optimized to prevent unnecessary recalculations
 * 
 * Usage Example:
 * ```php
 * #[ORM\Entity]
 * class User extends AbstractEntity
 * {
 *     #[Aggregatable(
 *         function: "sum",
 *         collection: "orders",
 *         aggregateField: "total",
 *         filters: ['status' => 'completed']
 *     )]
 *     #[AggregatableCriteria(field: "type", operator: "in", args: [["premium", "standard"]])]
 *     protected int $totalOrderValue = 0;
 * 
 *     #[OneToMany(mappedBy: "user", targetEntity: Order::class)]
 *     protected Collection $orders;
 * }
 * ```
 * 
 * Event Flow:
 * 1. Entity changes → postPersist/postUpdate events triggered
 * 2. Listener queues affected entities for recalculation
 * 3. postFlush event executes all queued calculations
 * 4. Aggregate fields updated with new calculated values
 * 
 * Supported Aggregate Functions:
 * - sum: Sum of numeric values
 * - count: Count of matching records
 * - avg: Average of numeric values
 * - min: Minimum value
 * - max: Maximum value
 * 
 * Filtering Options:
 * - Simple filters: ['status' => 'active']
 * - Join filters: ['category.name' => 'electronics']
 * - Criteria filters: Complex conditions using Doctrine Expr methods
 * 
 * Performance Considerations:
 * - Calculations are batched and executed in postFlush
 * - Entities in deletion queue are skipped to prevent unnecessary work
 * - Priority system ensures dependent fields are calculated in correct order
 * 
 * @package ExprAs\Doctrine\Behavior\Aggregatable
 * @author ExprAs Team
 * @since 1.0.0
 */
class AggregatableListener extends MappedEventSubscriber
{
    protected array $_queue = [];

    protected array $_deletionQueue = [];

    protected array $_deletedEntities = [];

    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
            Events::postPersist,
            Events::preRemove,
            Events::preFlush,
            Events::postUpdate,
            Events::preUpdate,
            Events::postFlush,
        ];
    }

    protected function queueMatched(object $entity, ObjectManager $objectManager): void
    {
        $entityName = $entity::class;

        $metaData = $objectManager->getClassMetadata($entityName);
        $assocNames = $metaData->getAssociationNames();
        foreach ($assocNames as $_assocField) {
            $targetClass = $metaData->getAssociationTargetClass($_assocField);
            if (($config = $this->getConfiguration($objectManager, $targetClass))) {
                foreach ($config as $_field => $_config) {

                    if ($_field === 'useObjectClass') {
                        continue;
                    }

                    $mapping = $_config->getMapping();

                    if (!($entity instanceof $mapping['targetEntity'])) {
                        continue;
                    }

                    if (!($targetEntity = $metaData->getFieldValue($entity, $mapping['mappedBy']))) {
                        continue;
                    }

                    if ($targetEntity instanceof Proxy) {
                        $targetEntity->__load();
                    }
                    $this->_queue[$targetEntity->getId()] ??= [];
                    $this->_queue[$targetEntity->getId()][$_field] = [
                        'entity' => $targetEntity,
                        'field'  => $_field,
                        'annot'  => $_config,
                    ];
                }
            }
        }

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

    public function postPersist(LifecycleEventArgs $eventArgs): void
    {
        $this->queueMatched($eventArgs->getObject(), $eventArgs->getObjectManager());
    }

    public function postUpdate(LifecycleEventArgs $eventArgs): void
    {
        $this->queueMatched($eventArgs->getObject(), $eventArgs->getObjectManager());
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs): void
    {
        /**
 * @var $config Ratable 
*/
        $entity = $eventArgs->getObject();
        if ($entity instanceof Proxy) {
            $entityName = get_parent_class($entity);
        } else {
            $entityName = $entity::class;
        }


        if (($config = $this->getConfiguration($eventArgs->getObjectManager(), $entityName))) {

            $dependingFields = array_filter($config, fn ($item) => $item instanceof Aggregatable);
            $changeSet = $eventArgs->getEntityChangeSet();

            foreach (array_intersect_key($dependingFields, $changeSet) as $_field => $_item) {
                $this->_queue[$entity->getId()] ??= [];
                $this->_queue[$entity->getId()][$_field] = [
                    'entity' => $entity,
                    'field'  => $_field,
                    'annot'  => $_item,
                ];
            }

        }

    }

    public function preFlush(PreFlushEventArgs $eventArgs): void
    {
        $em = $eventArgs->getObjectManager();
        $this->_deletionQueue += array_keys($em->getUnitOfWork()->getScheduledEntityDeletions());
    }

    public function preRemove(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getObject();
        $this->queueMatched($entity, $eventArgs->getObjectManager());
        
        // Store reference to deleted entity so we can detach it before nested flush
        $this->_deletedEntities[spl_object_id($entity)] = $entity;
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
     * @param PostFlushEventArgs $eventArgs
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function execute(PostFlushEventArgs $eventArgs): void
    {

        $em = $eventArgs->getEntityManager();
        $modifiedEntities = [];

        foreach ($this->_queue as $queue) {

            usort($queue, fn ($a, $b) => $b['annot']->getPriority() -  $a['annot']->getPriority());

            foreach ($queue as ['entity' => $entity, 'field' => $field, 'annot' => $annot]) {


                $metaData = $em->getClassMetadata($entity::class);
                /**
 * @var $metaData ClassMetadataInfo 
*/
                /**
 * @var $annot Aggregatable 
*/
                if ($metaData->getIdentifierValues($entity)) {

                    $teId = spl_object_id($entity);

                    // ignore if there is an entity in deletion queue
                    if (in_array($teId, $this->_deletionQueue)) {
                        continue;
                    }

                    $dqlParams = [
                        'e' => $entity
                    ];

                    $qb = $em->createQueryBuilder();
                    $qb->select(sprintf(sprintf('%s(e.%s)', $annot->getFunction(), $annot->getAggregateField())));
                    $qb->from($annot->getMapping()['targetEntity'], 'e');
                    $qb->andWhere(sprintf('e.%s = :e', $annot->getMapping()['mappedBy']));

                    foreach ($annot->getFilters() as $_field => $_value) {
                        if (str_contains((string) $_field, '.')) {
                            [$alias, $_field] = explode('.', (string) $_field);
                            $qb->join('e.' . $alias, $alias);
                            $qb->andWhere(sprintf($alias . '.%s = :%s', $_field, $_field));

                        } else {
                            $qb->andWhere(sprintf('e.%s = :%s', $_field, $_field));
                        }

                        $dqlParams[$_field] = $_value;


                    }

                    foreach ($annot->getCriteria() as $k => $criteriaParams) {

                        if (!method_exists(($expr = new Expr()), $operator = $criteriaParams->getOperator())) {
                            continue;
                        }

                        $criterion = $expr->{$operator}('e.' . $criteriaParams->getField(), ...$criteriaParams->getArgs());

                        if (is_array($criterion) && count($criterion) > 1 && method_exists(($expr = new Expr()), $criterion[1])) {
                            $_field = array_shift($criterion);
                            $operator = array_shift($criterion);
                            $criterion = $expr->{$operator}('e.' . $_field, ...$criterion);
                        }
                        if ($criteriaParams->isOr()) {
                            $qb->orWhere($criterion);
                        } else {
                            $qb->andWhere($criterion);
                        }

                    }

                    $query = $em->createQuery($qb->getDQL());
                    $query->setParameters($dqlParams);

                    $setterMethod = 'set' . ucfirst((string) $field);
                    if (!is_null($fieldVal = $query->getSingleScalarResult())) {
                        $entity->$setterMethod($fieldVal);
                    } else {
                        $entity->$setterMethod($annot->getDefault());
                    }

                    // Track modified entity for targeted flush
                    $modifiedEntities[] = $entity;

                }
            }
        }
        $this->_queue = [];
        $this->_deletionQueue = [];

        if (!empty($modifiedEntities)) {
            // Detach deleted entities to prevent cascade persist errors during nested flush
            foreach ($this->_deletedEntities as $deletedEntity) {
                if ($em->contains($deletedEntity)) {
                    $em->detach($deletedEntity);
                }
            }
            
            // Changed entities are already managed by EntityManager
            // Calling flush() will automatically persist their changes to the database
            // This includes cases where aggregates were recalculated due to deletions
            $em->flush();
        }

        // Clear deleted entities tracking after flush
        $this->_deletedEntities = [];
    }
}
