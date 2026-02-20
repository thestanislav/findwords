<?php

/**
 * QueryBuilderFilterHelper Usage Examples
 * 
 * This file demonstrates various ways to use the QueryBuilderFilterHelper.
 * DO NOT include this file in production - it's for documentation purposes only.
 */

namespace ExprAs\Doctrine\Helper\Examples;

use ExprAs\Doctrine\Helper\QueryBuilderFilterHelper;
use Doctrine\ORM\EntityManagerInterface;

class QueryBuilderFilterHelperExamples
{
    private QueryBuilderFilterHelper $filterHelper;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->filterHelper = new QueryBuilderFilterHelper($em);
    }

    /**
     * Example 1: Simple Equality Filters
     */
    public function simpleEqualityExample(): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('u')
            ->from('User', 'u');

        $filters = [
            'status' => 'active',
            'role' => 'admin',
        ];

        $this->filterHelper->applyFilters($qb, $filters, 'u');
        // Result: WHERE u.status = :status_0 AND u.role = :role_1

        return $qb->getQuery()->getResult();
    }

    /**
     * Example 2: IN Operator with Array
     */
    public function inOperatorExample(): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('p')
            ->from('Product', 'p');

        $filters = [
            'status' => ['active', 'featured', 'new'],
        ];

        $this->filterHelper->applyFilters($qb, $filters, 'p');
        // Result: WHERE p.status IN (:status_0)

        return $qb->getQuery()->getResult();
    }

    /**
     * Example 3: Comparison Operators
     */
    public function comparisonOperatorsExample(): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('p')
            ->from('Product', 'p');

        $filters = [
            'price' => [
                ['op' => 'gte', 'args' => [100]],
                ['op' => 'lte', 'args' => [1000]],
            ],
            'stock' => ['op' => 'gt', 'args' => [0]],
        ];

        $this->filterHelper->applyFilters($qb, $filters, 'p');
        // Result: WHERE p.price >= :price_0 
        //         AND p.price <= :price_1 
        //         AND p.stock > :stock_2

        return $qb->getQuery()->getResult();
    }

    /**
     * Example 4: BETWEEN Operator (Multi-arg)
     */
    public function betweenOperatorExample(): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('o')
            ->from('Order', 'o');

        $filters = [
            'totalAmount' => ['op' => 'between', 'args' => [50, 500]],
        ];

        $this->filterHelper->applyFilters($qb, $filters, 'o');
        // Result: WHERE o.totalAmount BETWEEN :totalAmount_0 AND :totalAmount_1

        return $qb->getQuery()->getResult();
    }

    /**
     * Example 5: LIKE Operator for Text Search
     */
    public function likeOperatorExample(): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('u')
            ->from('User', 'u');

        $filters = [
            'email' => ['op' => 'like', 'args' => ['gmail.com']],
            'firstName' => ['op' => 'like', 'args' => ['John']],
        ];

        $this->filterHelper->applyFilters($qb, $filters, 'u');
        // Result: WHERE u.email LIKE :email_0 
        //         AND u.firstName LIKE :firstName_1
        // Note: Values are auto-wrapped with %

        return $qb->getQuery()->getResult();
    }

    /**
     * Example 6: String Prefix/Suffix Matching
     */
    public function startsEndsExample(): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('u')
            ->from('User', 'u');

        $filters = [
            'username' => ['op' => 'starts', 'args' => ['admin_']],
            'email' => ['op' => 'ends', 'args' => ['@company.com']],
        ];

        $this->filterHelper->applyFilters($qb, $filters, 'u');
        // Result: WHERE SUBSTRING(u.username, 1, 6) = :username_0
        //         AND SUBSTRING(u.email, -12) = :email_1

        return $qb->getQuery()->getResult();
    }

    /**
     * Example 7: NULL Checks
     */
    public function nullCheckExample(): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('u')
            ->from('User', 'u');

        $filters = [
            'deletedAt' => ['op' => 'is', 'args' => ['null']],
            'verifiedAt' => ['op' => 'is', 'args' => ['notnull']],
        ];

        $this->filterHelper->applyFilters($qb, $filters, 'u');
        // Result: WHERE u.deletedAt IS NULL 
        //         AND u.verifiedAt IS NOT NULL

        return $qb->getQuery()->getResult();
    }

    /**
     * Example 8: OR Expressions
     */
    public function orExpressionExample(): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('u')
            ->from('User', 'u');

        $filters = [
            'status' => 'active',  // AND condition
            'role__or' => 'admin',  // OR condition
            'email__or' => ['op' => 'like', 'args' => ['@vip.com']],  // OR condition
        ];

        $this->filterHelper->applyFilters($qb, $filters, 'u');
        // Result: WHERE u.status = :status_0 
        //         OR (u.role = :role_1 OR u.email LIKE :email_2)

        return $qb->getQuery()->getResult();
    }

    /**
     * Example 9: Nested Relations (Single Level)
     */
    public function nestedRelationSingleExample(): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('p')
            ->from('Product', 'p');

        $filters = [
            'category.name' => 'Electronics',
            'category.active' => true,
        ];

        $this->filterHelper->applyFilters($qb, $filters, 'p');
        // Result: LEFT JOIN p.category p_category
        //         WHERE p_category.name = :p_category_name_0 
        //         AND p_category.active = :p_category_active_1

        return $qb->getQuery()->getResult();
    }

    /**
     * Example 10: Nested Relations (Multi-Level)
     */
    public function nestedRelationMultiLevelExample(): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('p')
            ->from('Product', 'p');

        $filters = [
            'category.parent.name' => 'Technology',
            'category.parent.active' => true,
        ];

        $this->filterHelper->applyFilters($qb, $filters, 'p');
        // Result: LEFT JOIN p.category p_category
        //         LEFT JOIN p_category.parent p_category_parent
        //         WHERE p_category_parent.name = :p_category_parent_name_0
        //         AND p_category_parent.active = :p_category_parent_active_1

        return $qb->getQuery()->getResult();
    }

    /**
     * Example 11: Regular Expression Matching
     */
    public function regexpExample(): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('u')
            ->from('User', 'u');

        $filters = [
            'phone' => ['op' => 'regexp', 'args' => ['^\\+1[0-9]{10}$']],
        ];

        $this->filterHelper->applyFilters($qb, $filters, 'u');
        // Result: WHERE REGEXP(u.phone, :phone_0) = 1

        return $qb->getQuery()->getResult();
    }

    /**
     * Example 12: Collection Membership
     */
    public function memberOfExample(): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('u')
            ->from('User', 'u');

        $filters = [
            'roles' => ['op' => 'member', 'args' => ['ROLE_ADMIN']],
        ];

        $this->filterHelper->applyFilters($qb, $filters, 'u');
        // Result: WHERE :roles_0 MEMBER OF u.roles

        return $qb->getQuery()->getResult();
    }

    /**
     * Example 13: Complex Multi-Filter Query
     */
    public function complexMultiFilterExample(): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('o')
            ->from('Order', 'o');

        $filters = [
            // Status filter
            'status' => ['pending', 'processing', 'shipped'],
            
            // Price range
            'totalAmount' => [
                ['op' => 'gte', 'args' => [100]],
                ['op' => 'lte', 'args' => [5000]],
            ],
            
            // Customer relationship
            'customer.active' => true,
            'customer.tier' => 'premium',
            
            // Date range (not deleted)
            'deletedAt' => ['op' => 'is', 'args' => ['null']],
            
            // Search in notes (OR condition)
            'notes__or' => ['op' => 'like', 'args' => ['urgent']],
            'internalNotes__or' => ['op' => 'like', 'args' => ['urgent']],
        ];

        $this->filterHelper->applyFilters($qb, $filters, 'o');

        return $qb->getQuery()
            ->setMaxResults(50)
            ->getResult();
    }

    /**
     * Example 14: Aggregatable Behavior Integration
     */
    public function aggregatableIntegrationExample($entity, array $dynamicFilters): float
    {
        $qb = $this->em->createQueryBuilder()
            ->select('SUM(t.amount)')
            ->from('Transaction', 't')
            ->where('t.user = :user')
            ->setParameter('user', $entity);

        // Base filters
        $filters = [
            'status' => 'approved',
            'type' => 'credit',
        ];

        // Merge with dynamic filters
        $filters = array_merge($filters, $dynamicFilters);

        $this->filterHelper->applyFilters($qb, $filters, 't');

        return (float) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Example 15: NOT IN Operator
     */
    public function notInOperatorExample(): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('u')
            ->from('User', 'u');

        $filters = [
            'status' => ['op' => 'nin', 'args' => [['banned', 'deleted', 'suspended']]],
        ];

        $this->filterHelper->applyFilters($qb, $filters, 'u');
        // Result: WHERE u.status NOT IN (:status_0)

        return $qb->getQuery()->getResult();
    }

    /**
     * Example 16: Reusing Helper for Multiple Queries
     */
    public function reuseHelperExample(): array
    {
        // First query
        $qb1 = $this->em->createQueryBuilder()
            ->select('u')
            ->from('User', 'u');
        
        $this->filterHelper->applyFilters($qb1, ['status' => 'active'], 'u');
        $activeUsers = $qb1->getQuery()->getResult();

        // Second query - helper resets internal state
        $qb2 = $this->em->createQueryBuilder()
            ->select('p')
            ->from('Product', 'p');
        
        $this->filterHelper->applyFilters($qb2, ['inStock' => true], 'p');
        $inStockProducts = $qb2->getQuery()->getResult();

        return [
            'users' => $activeUsers,
            'products' => $inStockProducts,
        ];
    }
}

