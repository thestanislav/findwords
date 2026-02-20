<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 14.04.2014
 * Time: 11:38
 */

namespace ExprAs\Doctrine\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query as ORMQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as ORMPaginator;
use ReflectionClass;
use Laminas\Paginator\Paginator as ZendPaginator;

class DefaultRepository extends EntityRepository
{
    /**
     * @param null $args
     *
     * @return object
     */
    public function createEntity($args = null)
    {
        $ref = new ReflectionClass($this->getClassName());
        $args = func_get_args();
        return $ref->newInstanceArgs($args);
    }

    public function saveEntity($entity)
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush($entity);
    }

    /**
     * @param  array | Criteria| QueryBuilder $criteria
     * @param  int                            $hydrate
     * @param  bool                           $fetchJoinCollection
     * @return ZendPaginator
     */
    public function createPaginatorBy($criteria, ?array $orderBy = null, $hydrate = ORMQuery::HYDRATE_OBJECT, $fetchJoinCollection = true)
    {

        if ($criteria instanceof QueryBuilder) {
            $qb = $criteria;
        } else {
            $qb = $this->createQueryBuilder('e');
            //$qb->select('e');
            //$qb->from($this->getEntityName(), 'e');

            if ($criteria instanceof Criteria) {
                $qb->addCriteria($criteria);
            } else {
                foreach ($criteria as $_k => $_v) {
                    $placeHolder = ':' . preg_replace('~[^a-z]+~i', '', (string) $_k);
                    $qb->andWhere(sprintf('e.%s = %s', $_k, $placeHolder));
                    $qb->setParameter($placeHolder, $_v);
                }
            }
        }
        if ($orderBy) {
            foreach ($orderBy as $_k => $_v) {
                if (is_numeric($_k)) {
                    $qb->addOrderBy('e.' . $_v);
                } else {
                    $qb->addOrderBy('e.' . $_k, $_v);
                }
            }
        }

        if ($hydrate == null) {
            $hydrate = ORMQuery::HYDRATE_OBJECT;
        }

        $qb->getQuery()->setHydrationMode($hydrate);

        return new ZendPaginator(new ORMPaginator(new Paginator($qb, $fetchJoinCollection)));
    }

    /**
     * @param $dql
     * @param array $bindParams
     * @param int   $hydrate
     * @param bool  $fetchJoinCollection
     *
     * @return ZendPaginator
     */
    public function createPaginatorByDql($dql, $bindParams = [], $hydrate = ORMQuery::HYDRATE_OBJECT, $fetchJoinCollection = true)
    {
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameters($bindParams);
        if ($hydrate == null) {
            $hydrate = ORMQuery::HYDRATE_OBJECT;
        }
        $query->setHydrationMode($hydrate);
        return new ZendPaginator(new ORMPaginator(new Paginator($query, $fetchJoinCollection)));
    }

    /**
     * @return ZendPaginator
     */
    public function createPaginator()
    {
        $dql = sprintf('select e from %s e', $this->getEntityName());
        return $this->createPaginatorByDql($dql, [], null, false);
    }


    /**
     * @param $dql
     * @param array $bindParams
     * @param null  $limit
     * @param null  $offset
     * @param int   $hydrate
     *
     * @return array|\ArrayIterator|\Traversable
     */
    public function findByDql($dql, $bindParams = [], $limit = null, $offset = null, $hydrate = ORMQuery::HYDRATE_OBJECT)
    {
        return $this->createPaginatorByDql($dql, $bindParams, $hydrate, false)->getAdapter()->getItems($offset, $limit);
    }

    /**
     * @param $dql
     * @param array $bindParams
     * @param int   $hydrate
     *
     * @return array
     */
    public function findAllByDql($dql, $bindParams = [], $hydrate = ORMQuery::HYDRATE_OBJECT)
    {
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameters($bindParams);
        if ($hydrate == null) {
            $hydrate = ORMQuery::HYDRATE_OBJECT;
        }
        $query->setHydrationMode($hydrate);

        return $query->getResult($hydrate);
    }
}
