<?php

namespace ExprAs\Doctrine\Behavior\Aggregatable\Mapping\Annotation;

use Doctrine\Common\Collections\Criteria;
use Gedmo\Mapping\Annotation\Annotation as GedmoAnnotation;

/**
 * @DoctrineAnnotation\NamedArgumentConstructor
 * @DoctrineAnnotation\Target("PROPERTY")
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Aggregatable implements GedmoAnnotation
{
    public const string FUNCTION_AVG = 'AVG';
    public const string FUNCTION_SUM = 'SUM';
    public const string FUNCTION_MIN = 'MIN';
    public const string FUNCTION_MAX = 'MAX';
    public const string FUNCTION_COUNT = 'COUNT';

    /**
     * @var AggregatableCriteria[]
     */
    private array $criteria = [];

    private array $mapping;

    /**
     * @param string  $function
     * @param string  $aggregateField
     * @param string  $collection
     * @param mixed[] $filters
     * @param string $default
     * @param int $priority
     */
    public function __construct(
        /**
         * @psalm-var    "AVG" | "SUM" | "MIN" | "MAX" | "COUNT"
         * @Enum({"AVG", "SUM", "MIN", "MAX", "COUNT"})
         */
        private readonly string $function,
        private readonly string $collection,
        private readonly string $aggregateField = 'id',
        private readonly array $filters = [],
        private readonly mixed $default = 0,
        private readonly int $priority = 1
    )
    {
    }


    /**
     * @return string
     */
    public function getFunction(): string
    {
        return $this->function;
    }

    /**
     * @return string
     */
    public function getCollection(): string
    {
        return $this->collection;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @return array
     */
    public function getMapping(): array
    {
        return $this->mapping;
    }

    /**
     * @param array $mapping
     */
    public function setMapping(array $mapping): void
    {
        $this->mapping = $mapping;
    }

    /**
     * @return string
     */
    public function getAggregateField(): string
    {
        return $this->aggregateField;
    }

    /**
     * @return AggregatableCriteria[]
     */
    public function getCriteria(): array
    {
        return $this->criteria;
    }

    /**
     * @param array $criteria
     */
    public function setCriteria(array $criteria): void
    {
        $this->criteria = $criteria;
    }

    /**
     * @return string
     */
    public function getDefault(): string
    {
        return $this->default;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }


}
