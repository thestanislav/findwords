<?php

namespace ExprAs\Doctrine\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;

/**
 * QueryBuilderFilterHelper - Apply Filters to Doctrine QueryBuilder
 * 
 * This helper provides a flexible way to apply filters to Doctrine QueryBuilder instances.
 * It supports various filter formats, nested relations, OR expressions, and all Doctrine Expr operators.
 * 
 * Supported Filter Formats:
 * - Simple equality: ['field' => 'value']
 * - Simple list (IN): ['field' => [1, 2, 3]]
 * - Single condition: ['field' => ['op' => 'like', 'args' => ['search']]]
 * - Multiple conditions: ['field' => [['op' => 'gt', 'args' => [5]], ['op' => 'lt', 'args' => [10]]]]
 * - Multi-argument operators: ['age' => ['op' => 'between', 'args' => [18, 65]]]
 * - OR expressions: ['field__or' => ['op' => 'eq', 'args' => ['x']]]
 * - Nested relations: ['category.name' => 'Electronics']
 * 
 * Special Operators:
 * - like/notLike: Auto-wraps value with % wildcards
 * - in/nin: Expects array as argument
 * - is: Converts to isNull() or isNotNull()
 * - starts/ends: Uses SUBSTRING for prefix/suffix matching
 * - regexp/regex/neregexp/neregex/notregex: Uses REGEXP DQL function
 * - member: Uses MEMBER OF for collection membership
 * - All other Doctrine Expr methods via reflection
 * 
 * Example Usage:
 * ```php
 * $helper = new QueryBuilderFilterHelper($entityManager);
 * $qb = $entityManager->createQueryBuilder()
 *     ->select('u')
 *     ->from(User::class, 'u');
 * 
 * $filters = [
 *     'status' => 'active',
 *     'age' => ['op' => 'between', 'args' => [18, 65]],
 *     'category.name' => 'Electronics',
 *     'email__or' => ['op' => 'like', 'args' => ['@gmail.com']],
 * ];
 * 
 * $helper->applyFilters($qb, $filters, 'u');
 * ```
 * 
 * @package ExprAs\Doctrine\Helper
 */
class QueryBuilderFilterHelper
{
    /**
     * Parameters to be bound to the query.
     */
    protected array $params = [];

    /**
     * Counter for generating unique parameter names.
     */
    protected int $paramCounter = 0;

    /**
     * Cache of created joins to avoid duplicates.
     */
    protected array $joinCache = [];

    public function __construct(
        protected readonly EntityManagerInterface $entityManager
    ) {}

    /**
     * Apply filters to a QueryBuilder instance.
     * 
     * @param QueryBuilder $qb The QueryBuilder to modify
     * @param array $filters Array of filters to apply
     * @param string $alias The root entity alias (default: 'e')
     * @return QueryBuilder The modified QueryBuilder (fluent interface)
     */
    public function applyFilters(
        QueryBuilder $qb,
        array $filters,
        string $alias = 'e'
    ): QueryBuilder {
        // Reset state for new filter application
        $this->params = [];
        $this->paramCounter = 0;
        $this->joinCache = [];

        $exprAnd = $qb->expr()->andX();
        $exprOr = $qb->expr()->orX();

        // Normalize and process filters
        $normalizedFilters = $this->normalizeFilters($filters);

        foreach ($normalizedFilters as $fieldPath => $conditions) {
            // Determine if this is an OR condition
            $isOr = str_ends_with($fieldPath, '__or');
            if ($isOr) {
                $fieldPath = substr($fieldPath, 0, -4);
            }

            $expr = $isOr ? $exprOr : $exprAnd;

            // Process each condition for this field
            $this->processField($qb, $fieldPath, $conditions, $alias, $expr);
        }

        // Add expressions to query
        if ($exprAnd->count() > 0) {
            $qb->andWhere($exprAnd);
        }
        if ($exprOr->count() > 0) {
            $qb->orWhere($exprOr);
        }

        // Bind all parameters
        if (!empty($this->params)) {
            $qb->setParameters($this->params);
        }

        return $qb;
    }

    /**
     * Normalize filters to a consistent format.
     * 
     * @param array $filters Raw filters
     * @return array Normalized filters
     */
    protected function normalizeFilters(array $filters): array
    {
        $normalized = [];

        foreach ($filters as $field => $value) {
            $normalized[$field] = $this->normalizeCondition($value);
        }

        return $normalized;
    }

    /**
     * Normalize a single condition to standard format.
     * 
     * @param mixed $condition The condition to normalize
     * @return array Array of normalized conditions
     */
    protected function normalizeCondition(mixed $condition): array
    {
        // Check if it's already a properly formatted condition
        if (is_array($condition) && isset($condition['op'])) {
            // Ensure args is an array
            if (!isset($condition['args'])) {
                $condition['args'] = [];
            } elseif (!is_array($condition['args'])) {
                $condition['args'] = [$condition['args']];
            }
            return [$condition];
        }

        // Check if it's multiple conditions
        if ($this->isMultipleConditions($condition)) {
            $result = [];
            foreach ($condition as $cond) {
                $result = array_merge($result, $this->normalizeCondition($cond));
            }
            return $result;
        }

        // Check if it's a list of values (for IN operator)
        if ($this->isListOfValues($condition)) {
            return [['op' => 'in', 'args' => [$condition]]];
        }

        // Simple value - convert to equality
        if ($this->isSimpleValue($condition)) {
            return [['op' => 'eq', 'args' => [$condition]]];
        }

        // Fallback to equality
        return [['op' => 'eq', 'args' => [$condition]]];
    }

    /**
     * Check if value is multiple conditions.
     */
    protected function isMultipleConditions(mixed $value): bool
    {
        if (!is_array($value) || empty($value)) {
            return false;
        }

        // Check if it's a list of condition objects
        $allConditions = true;
        foreach ($value as $item) {
            if (!is_array($item) || !isset($item['op'])) {
                $allConditions = false;
                break;
            }
        }

        return $allConditions;
    }

    /**
     * Check if value is a simple scalar value.
     */
    protected function isSimpleValue(mixed $value): bool
    {
        return is_scalar($value) || $value === null;
    }

    /**
     * Check if value is a list of values (for IN operator).
     */
    protected function isListOfValues(mixed $value): bool
    {
        if (!is_array($value) || empty($value)) {
            return false;
        }

        // Check if it's a numeric array of simple values
        if (!array_is_list($value)) {
            return false;
        }

        // Check if all values are simple
        foreach ($value as $item) {
            if (!$this->isSimpleValue($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Process a field and its conditions.
     */
    protected function processField(
        QueryBuilder $qb,
        string $fieldPath,
        array $conditions,
        string $alias,
        Andx|Orx $expr
    ): void {
        // Handle nested fields (e.g., category.name)
        if (str_contains($fieldPath, '.')) {
            [$finalAlias, $finalField] = $this->handleNestedField($qb, $fieldPath, $alias);
        } else {
            $finalAlias = $alias;
            $finalField = $fieldPath;
        }

        // Apply each condition
        foreach ($conditions as $condition) {
            $operator = $condition['op'];
            $args = $condition['args'];

            $this->addCondition($expr, $operator, $finalAlias . '.' . $finalField, $args, $finalAlias, $finalField);
        }
    }

    /**
     * Handle nested field paths (e.g., category.parent.name).
     * Creates necessary JOINs and returns the final alias and field.
     * 
     * @return array [finalAlias, finalField]
     */
    protected function handleNestedField(
        QueryBuilder $qb,
        string $fieldPath,
        string $baseAlias
    ): array {
        $parts = explode('.', $fieldPath);
        $finalField = array_pop($parts);
        $currentAlias = $baseAlias;

        // Create JOINs for each part except the last (which is the field)
        foreach ($parts as $part) {
            $newAlias = $currentAlias . '_' . $part;
            $joinPath = $currentAlias . '.' . $part;

            if (!$this->isJoinExists($qb, $joinPath)) {
                $qb->leftJoin($joinPath, $newAlias);
                $this->joinCache[$joinPath] = $newAlias;
            }

            $currentAlias = $newAlias;
        }

        return [$currentAlias, $finalField];
    }

    /**
     * Add a condition to the expression.
     */
    protected function addCondition(
        Andx|Orx $expr,
        string $operator,
        string $fullFieldPath,
        array $args,
        string $alias,
        string $field
    ): void {
        $exprObj = new Expr();
        $paramName = $this->generateParamName($fullFieldPath);

        // Handle special operators
        switch ($operator) {
            case 'like':
            case 'notLike':
                $expr->add($exprObj->$operator($fullFieldPath, ':' . $paramName));
                $this->params[$paramName] = '%' . ($args[0] ?? '') . '%';
                break;

            case 'in':
                $expr->add($exprObj->in($fullFieldPath, ':' . $paramName));
                $this->params[$paramName] = $args[0] ?? [];
                break;

            case 'nin':
                $expr->add($exprObj->notIn($fullFieldPath, ':' . $paramName));
                $this->params[$paramName] = $args[0] ?? [];
                break;

            case 'is':
                // Convert to isNull or isNotNull
                $value = strtolower((string)($args[0] ?? 'null'));
                if ($value === 'null' || $value === 'notnull') {
                    $method = 'is' . ucfirst($value);
                    if (method_exists($exprObj, $method)) {
                        $expr->add($exprObj->$method($fullFieldPath));
                    }
                }
                break;

            case 'starts':
                // Use SUBSTRING for prefix matching
                $length = mb_strlen((string)($args[0] ?? ''));
                $substringExpr = $exprObj->substring($fullFieldPath, 1, $length);
                $expr->add($exprObj->eq($substringExpr, ':' . $paramName));
                $this->params[$paramName] = $args[0] ?? '';
                break;

            case 'ends':
                // Use SUBSTRING for suffix matching
                $length = mb_strlen((string)($args[0] ?? ''));
                $substringExpr = $exprObj->substring($fullFieldPath, -1 * $length);
                $expr->add($exprObj->eq($substringExpr, ':' . $paramName));
                $this->params[$paramName] = $args[0] ?? '';
                break;

            case 'regexp':
            case 'regex':
                $expr->add("REGEXP(" . $fullFieldPath . ", :" . $paramName . ") = 1");
                $this->params[$paramName] = $args[0] ?? '';
                break;

            case 'neregexp':
            case 'neregex':
            case 'notregex':
                $expr->add("REGEXP(" . $fullFieldPath . ", :" . $paramName . ") = 0");
                $this->params[$paramName] = $args[0] ?? '';
                break;

            case 'member':
                $expr->add(':' . $paramName . ' MEMBER OF ' . $fullFieldPath);
                $this->params[$paramName] = $args[0] ?? null;
                break;

            default:
                // Dynamic operator handling via reflection
                if (method_exists($exprObj, $operator)) {
                    $this->applyDynamicOperator($expr, $exprObj, $operator, $fullFieldPath, $args, $paramName);
                }
                break;
        }
    }

    /**
     * Apply a dynamic operator using reflection.
     */
    protected function applyDynamicOperator(
        Andx|Orx $expr,
        Expr $exprObj,
        string $operator,
        string $fullFieldPath,
        array $args,
        string $paramName
    ): void {
        $ref = new \ReflectionMethod($exprObj, $operator);
        $paramCount = $ref->getNumberOfParameters();

        // For methods that need multiple parameters
        if ($paramCount > 2) {
            // Create parameter names for each argument
            $paramNames = [];
            foreach ($args as $i => $arg) {
                $pName = $paramName . '_' . $i;
                $paramNames[] = ':' . $pName;
                $this->params[$pName] = $arg;
            }

            // Invoke with field and all parameter names
            $result = $ref->invoke($exprObj, $fullFieldPath, ...$paramNames);
            if ($result) {
                $expr->add($result);
            }
        } else {
            // Simple two-parameter methods (field, value)
            $result = $ref->invoke($exprObj, $fullFieldPath, ':' . $paramName);
            if ($result) {
                $expr->add($result);
            }
            $this->params[$paramName] = $args[0] ?? null;
        }
    }

    /**
     * Generate a unique parameter name.
     */
    protected function generateParamName(string $fieldPath): string
    {
        $sanitized = str_replace('.', '_', $fieldPath);
        return $sanitized . '_' . ($this->paramCounter++);
    }

    /**
     * Check if a JOIN already exists in the QueryBuilder.
     */
    protected function isJoinExists(QueryBuilder $qb, string $joinPath): bool
    {
        // Check cache first
        if (isset($this->joinCache[$joinPath])) {
            return true;
        }

        // Check DQL
        return str_contains($qb->getDQL(), $joinPath);
    }
}

