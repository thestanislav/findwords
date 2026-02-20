<?php

namespace ExprAs\Rest\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use ExprAs\Rest\Mappings\Queryable;
use Laminas\Stdlib\ArrayUtils;

class GetListFilterHelper
{
    protected array $params = [];
    private Expr\Andx $exprAnd;
    private Expr\Orx $exprOr;

    protected array $subQueryBuildersCache = [];

    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly QueryBuilder $queryBuilder,
        protected readonly string $entityClassName,
        protected readonly string $alias = 'e'
    ) {
        $this->exprOr = $queryBuilder->expr()->orX();
        $this->exprAnd = $queryBuilder->expr()->andx();
    }


    /**
     * Get the EntityManager.
     *
     * @return EntityManagerInterface The EntityManager instance.
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * Get the QueryBuilder instance.
     *
     * @return QueryBuilder The QueryBuilder instance.
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    protected function isMultipleFilterFieldFilter($params): bool
    {
        return is_array($params)
            && count($params) === count(
                array_filter(
                    $params,
                    fn($v) => is_array($v) && count(array_intersect(array_keys($v), ['operator', 'value'])) === 2
                )
            );
    }


    public function addWhere($where)
    {
        foreach ($where as $key => $params) {
            if (str_starts_with((string) $key, '__query') || $key === 'q') {
                $this->processSearchQuery($key, $params);
            } else {

                if (is_array($params) && $params && array_key_exists('operator', $params)) {
                    $params = [$params];
                } elseif (ArrayUtils::isList($params) && !$this->isMultipleFilterFieldFilter($params)) {
                    $params = [['operator' => 'in', 'value' => $params]];
                } elseif (!is_array($params) && !$this->isMultipleFilterFieldFilter($params)) {
                    $params = [['operator' => 'eq', 'value' => $params]];
                }


                foreach ($params as $value) {

                    if (is_array($value) && isset($value['operator']) && array_key_exists('value', $value)) {
                        $this->handleConditions(
                            $this->getQueryBuilder(),
                            $key,
                            $value['operator'] ?? 'eq',
                            $value['value'],
                            $this->alias,
                            $this->entityManager->getClassMetadata($this->entityClassName),
                            $this->exprAnd,
                            $this->exprOr
                        );
                    }
                }

            }
        }

        if ($this->exprAnd->count()) {
            $this->queryBuilder->andWhere($this->exprAnd);
        }
        if ($this->exprOr->count()) {
            $this->queryBuilder->orWhere($this->exprOr);
        }

        $this->queryBuilder->setParameters($this->params);
    }

    public function addOrder($associationPath, $order)
    {
        $currentAlias = $this->alias;
        $metaData = $this->getEntityManager()->getClassMetadata($this->entityClassName);

        while (!empty($associationPath)) {
            [$field, $associationPath] = array_pad(explode('.', (string) $associationPath, 2), 2, null);

            if ($metaData->hasAssociation($field)) {
                $alias = $currentAlias . '_' . $field;
                $joinCondition = $currentAlias . '.' . $field . ' ' . $alias;

                if (!$this->isJoinAlreadyAdded($joinCondition)) {
                    $this->getQueryBuilder()->leftJoin($currentAlias . '.' . $field, $alias);
                }

                $currentAlias = $alias;
                $metaData = $this->getEntityManager()->getClassMetadata($metaData->getAssociationTargetClass($field));
            } elseif ($metaData->hasField($field)) {
                $this->getQueryBuilder()->addOrderBy($currentAlias . '.' . $field, $order);
                break; // Stop the loop if we've added an order by a field
            } else {
                // Possibly log or handle the case where the field or association doesn't exist
                break;
            }
        }
    }

    protected function isJoinAlreadyAdded($joinCondition)
    {
        // Check if the join condition already exists in the DQL
        return str_contains($this->getQueryBuilder()->getDQL(), (string) $joinCondition);
    }

    protected function getSearchableFields(ClassMetadata $metadata): array
    {

        $out = [];
        $class = new \ReflectionClass($metadata->getName());
        if (count($attributes = $class->getAttributes(Queryable::class, \ReflectionAttribute::IS_INSTANCEOF))) {
            $attribute = $attributes[0];
            /**
             * @var Queryable $instance
             */
            $instance = $attribute->newInstance();
            $fieldNames = array_merge($metadata->getFieldNames(), $metadata->getAssociationNames());
            if (count($instance->getFields())) {
                $out = array_intersect($instance->getFields(), $fieldNames);
            }
        };

        if (count($out)) {
            return $out;
        }

        return array_filter($metadata->getFieldNames(), fn($_fieldName) => in_array($metadata->getTypeOfField($_fieldName), ['text', 'string']));
    }

    /**
     * Process search queries with $$query indicator.
     *
     * @param string $key   The key indicating a search query.
     * @param mixed  $value The search value.
     *
     * @throws \ReflectionException
     */
    protected function processSearchQuery(string $key, $value): void
    {

        $expr = new Expr\Orx();

        $metadata = $this->entityManager->getClassMetadata($this->entityClassName);

        if (is_array($value) && array_key_exists('operator', $value) && array_key_exists('value', $value)) {
            ['operator' => $operator, 'value' => $value] = $value;
        } else {
            $operator = 'like';
        }

        foreach ($this->getSearchableFields($metadata) as $_fieldName) {

            if ($metadata->hasAssociation($_fieldName)) {
                $mapping = $metadata->getAssociationMapping($_fieldName);
                $assocClassMetadata = $this->getEntityManager()->getClassMetadata($mapping['targetEntity']);
                $assocExpr = new Expr\Orx();
                if ($mapping['type'] & ClassMetadataInfo::TO_ONE) {
                    foreach ($this->getSearchableFields($assocClassMetadata) as $_assocFieldName) {
                        $this->handleToOneAssociations(
                            $this->getQueryBuilder(),
                            $assocExpr,
                            $mapping['targetEntity'],
                            $mapping['fieldName'] . '.' . $_assocFieldName . '__or',
                            $operator,
                            $value,
                            $this->alias
                        );
                    }
                } elseif ($mapping['type'] & ClassMetadataInfo::TO_MANY) {

                    foreach ($this->getSearchableFields($assocClassMetadata) as $_assocFieldName) {
                        $this->handleToManyAssociations(
                            $assocExpr,
                            $mapping,
                            $_assocFieldName . '__or',
                            $operator,
                            $value,
                            $this->alias
                        );
                    }
                }
                $expr->add($assocExpr);
            } else {
                $expr->add(call_user_func_array([new Expr(), $operator], [$this->alias . '.' . $_fieldName, ':__query__']));
                $this->params['__query__'] = "%$value%";
            }

        }

        if ($expr->count()) {
            (str_ends_with($key, '__or') ? $this->exprOr : $this->exprAnd)->add($expr);
            //$this->exprOr->add($expr);
        }
    }


    /**
     * Handle conditions for field paths, including associations.
     *
     * @param QueryBuilder  $queryBuilder The QueryBuilder to modify.
     * @param string        $fieldPath    The path to the field.
     * @param string        $operator     The comparison operator.
     * @param mixed         $value        The value to compare.
     * @param string        $alias        The alias for the entity in the query.
     * @param ClassMetadata $metadata     Metadata for the entity.
     * @param Andx|null     $exprAnd      Expression for AND conditions.
     * @param Orx|null      $exprOr       Expression for OR conditions.
     */
    protected function handleConditions(
        QueryBuilder $queryBuilder,
        string $fieldPath,
        string $operator,
        mixed $value,
        string $alias,
        ClassMetadata $metadata,
        ?Expr\Andx &$exprAnd = null,
        ?Expr\Orx &$exprOr = null,
    ): void {
        if (str_ends_with($fieldPath, '__or')) {
            $expr = $exprOr ?? $queryBuilder->expr()->orX();
            $fieldPath = substr($fieldPath, 0, -4);
        } else {
            $expr = $exprAnd ?? $queryBuilder->expr()->andX();
        }

        if (!str_contains($fieldPath, '.') && !$metadata->hasAssociation($fieldPath)) {
            $fieldName = $fieldPath;
            $associationPath = '';
        } elseif ($metadata->hasAssociation($fieldPath)) {
            $fieldName = $fieldPath;
            $associationPath = 'id'; // todo: find a better way to handle this
            if ($metadata->isSingleValuedAssociation($fieldPath)) { // todo: find a better way to handle this
                $fieldPath = $fieldName . '.id'; // todo: find a better way to handle this
            }
        } else {
            [$fieldName, $associationPath] = explode('.', $fieldPath, 2);
        }


        $associationMappings = $metadata->getAssociationMappings();

        if ($associationPath && isset($associationMappings[$fieldName]) && $associationMappings[$fieldName]['type'] & ClassMetadataInfo::TO_MANY) {
            $this->handleToManyAssociations(
                $expr,
                $associationMappings[$fieldName],
                $associationPath,
                $operator,
                $value,
                $alias,
            );
        } elseif ($associationPath && isset($associationMappings[$fieldName])) {
            $this->handleToOneAssociations(
                $queryBuilder,
                $expr,
                $associationMappings[$fieldName]['targetEntity'],
                $fieldPath,
                $operator,
                $value,
                $alias,
            );
        } elseif ($metadata->hasField($fieldName) || $metadata->hasAssociation($fieldName)) {

            $paramName = str_replace('.', '_', $fieldPath);
            $this->addExpression($expr, $operator, $fieldName, $value, $alias, $paramName);

        }

    }


    /**
     * Add a conditional expression to the query based on the operator and value.
     *
     * @param Expr\Andx|Expr\Orx $expr      The expression to modify.
     * @param string             $operator  The comparison operator.
     * @param string             $field     The field to compare.
     * @param mixed              $value     The value for comparison.
     * @param string             $alias     The alias for the entity in the query.
     * @param string             $paramName The name of the parameter.
     *
     * @throws \ReflectionException
     */
    protected function addExpression(Expr\Andx|Expr\Orx $expr, string $operator, string $field, mixed $value, string $alias, string $paramName): void
    {


        if ($operator == 'regexp' || $operator == 'regex') {
            $expr->add("REGEXP(" . $alias . ' . ' . $field . ",:$paramName) = 1");
            $this->params[$paramName] = $value;
        } elseif ($operator == 'neregexp' || $operator == 'neregex' || $operator == 'notregex') {
            $expr->add("REGEXP(" . $alias . ' . ' . $field . ",:$paramName) = 0");
            $this->params[$paramName] = $value;
        } elseif ($operator == 'member') {
            $expr->add(":$paramName" . ' MEMBER OF ' . $alias . '.' . $field);
            $this->params[$paramName] = $value;
        } elseif ($operator == 'like' || $operator == 'notLike') {
            $expr->add((new Expr())->$operator($alias . ' . ' . $field, ":$paramName"));
            $this->params[$paramName] = '%' . $value . '%';
        } elseif ($operator == 'is') {
            $method = 'is' . ucfirst((string) $value);
            $_expr = new Expr();
            if (method_exists($_expr, $method)) {
                $expr->add($_expr->{$method}($alias . '.' . $field));
            }

        } elseif ($operator == 'starts') {

            $_expr = new Expr();
            $_substr = new Expr();
            $expr->add(
                $_expr->eq(
                    $_substr->substring($alias . '.' . $field, 1, mb_strlen((string) $value)),
                    ":$paramName"
                )
            );
            $this->params[$paramName] = $value;

        } elseif ($operator == 'ends') {

            $_expr = new Expr();
            $_substr = new Expr();
            $expr->add(
                $_expr->eq(
                    $_substr->substring($alias . '.' . $field, -1 * mb_strlen((string) $value)),
                    ":$paramName"
                )
            );
            $this->params[$paramName] = $value;

        } elseif ($operator === 'nin') {
            $expr->add(
                (new Expr())->notIn($alias . '.' . $field, ":$paramName")
            );
            $this->params[$paramName] = (array)$value;
        } elseif (method_exists(($_expr = new Expr()), $operator)) {

            $ref = new \ReflectionObject($_expr);
            $method = $ref->getMethod($operator);
            if ($method->getNumberOfParameters() > 2) {
                $expr->add(
                    $method->invokeArgs(
                        $_expr,
                        [$alias . '.' . $field, ...array_map(fn($p) => ":$paramName$p", range(1, is_array($value) ? count($value) : 1))]
                    )
                );

                $c = 1;
                foreach ((array)$value as $_v) {
                    $this->params[$paramName . ($c++)] = $_v;
                }
            } else {
                $expr->add(
                    $method->invokeArgs($_expr, [$alias . '.' . $field, ":$paramName"])
                );
                $this->params[$paramName] = $value;
            }


        }

    }

    /**
     * Handle associations with a "to-many" relationship.
     *
     * @param Orx|Andx $expr            The expression to modify.
     * @param array    $associationMappings
     * @param string   $associationPath The path to the associated entity.
     * @param string   $operator        The comparison operator.
     * @param mixed    $value           The value for comparison.
     * @param string   $alias
     */
    protected function handleToManyAssociations(
        $expr,
        array $associationMappings,
        string $associationPath,
        string $operator,
        mixed $value,
        string $alias,
    ): void {
        $targetEntity = $associationMappings['targetEntity'];

        if ($associationMappings['isOwningSide']) {
            $mappedBy = $associationMappings['inversedBy'];
        } else {
            $mappedBy = $associationMappings['mappedBy'];
        }


        $subAlias = $alias . '_' . strtolower((string) preg_replace('~[^A-Za-z0-9]+~', '_', (string) $targetEntity)) . '_' . $associationMappings['fieldName'];;
        if (!isset($this->subQueryBuildersCache[$subAlias])) {
            $this->subQueryBuildersCache[$subAlias] = [];
        }

        // Check if we already have a subquery builder for this target entity and alias.
        if (!isset($this->subQueryBuildersCache[$subAlias]['query'])) {
            // Create a new subquery builder and store it.
            $subQueryBuilder = $this->entityManager->createQueryBuilder();
            $subQueryBuilder->select($subAlias)
                ->from($targetEntity, $subAlias);
            if ($associationMappings['type'] === ClassMetadataInfo::MANY_TO_MANY) {
                $subQueryBuilder->andWhere($subQueryBuilder->expr()->isMemberOf($alias, $subAlias . '.' . $mappedBy));
            } else {
                $subQueryBuilder->andWhere($subQueryBuilder->expr()->eq($subAlias . '.' . $mappedBy, $alias));
            }

            $expr->add((new Expr())->exists($subQueryBuilder));
            $this->subQueryBuildersCache[$subAlias]['query'] = $subQueryBuilder;
        } else {
            // Retrieve the existing subquery builder.
            $subQueryBuilder = $this->subQueryBuildersCache[$subAlias]['query'];
        }

        $exprOr = $subQueryBuilder->expr()->orX();
        $exprAnd = $subQueryBuilder->expr()->andx();

        $metaData = $this->getEntityManager()->getClassMetadata($targetEntity);
        $this->handleConditions(
            $subQueryBuilder,
            $associationPath,
            $operator,
            $value,
            $subAlias,
            $metaData,
            $exprAnd,
            $exprOr,
        );

        /**
         * @var Andx $where
         */
        $where = $subQueryBuilder->getDQLPart('where');
        if ($where->count() === 1) {
            $where->addMultiple([$exprOr, $exprAnd]);
        } else {
            foreach ($where->getParts() as $_part) {
                if ($_part instanceof Andx) {
                    $_part->add($exprAnd);
                } elseif ($_part instanceof Orx) {
                    $_part->add($exprOr);
                }
            }
        }


    }

    /**
     * Handle associations with a "to-one" relationship.
     *
     * @param QueryBuilder       $queryBuilder    The QueryBuilder to modify.
     * @param Expr\Orx|Expr\Andx $expr            The expression to modify.
     * @param string             $targetEntity    The target entity class name.
     * @param string             $associationPath The path to the associated entity.
     * @param string             $operator        The comparison operator.
     * @param mixed              $value           The value for comparison.
     */
    protected function handleToOneAssociations(
        QueryBuilder $queryBuilder,
        $expr,
        string $targetEntity,
        string $associationPath,
        string $operator,
        mixed $value,
        string $alias,
    ): void {


        if (!str_contains($associationPath, '.')) {
            $part = $associationPath;
            $associationPath = '';
        } else {
            [$part, $associationPath] = explode('.', $associationPath, 2);
        }

        $subAlias = $alias . '_' . $part;
        $joinCondition = $alias . '.' . $part . ' ' . $subAlias;

        if (!$this->isJoinAlreadyAdded($joinCondition)) {
            $queryBuilder->leftJoin($alias . '.' . $part, $subAlias);
        }

        $exprOr = $queryBuilder->expr()->orX();
        $exprAnd = $queryBuilder->expr()->andx();

        $this->handleConditions(
            $queryBuilder,
            $associationPath,
            $operator,
            $value,
            $subAlias,
            $this->getEntityManager()->getClassMetadata($targetEntity),
            $exprAnd,
            $exprOr,
        );
        $expr->add($exprAnd)->add($exprOr);
    }

}
