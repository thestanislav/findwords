<?php

namespace ExprAs\Doctrine\Behavior\Singleable\Mapping\Annotation;

use Gedmo\Mapping\Annotation\Annotation as GedmoAnnotation;

/**
 * Singleable Attribute/Annotation
 * 
 * Marks a field to ensure only one entity can have the specified value within a group.
 * 
 * Example usage:
 * 
 * ```php
 * #[Singleable(
 *     value: true,
 *     cancelValue: false,
 *     group: ['userId']
 * )]
 * private bool $isDefault;
 * ```
 * 
 * @DoctrineAnnotation\NamedArgumentConstructor
 * @DoctrineAnnotation\Target("PROPERTY")
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Singleable implements GedmoAnnotation
{
    /**
     * @param mixed $value The value that should be exclusive (e.g., true for default flag)
     * @param bool $cancelEscorted Whether to reset other entities when this one gets the value
     * @param mixed $cancelValue The value to set on other entities (e.g., false for boolean fields)
     * @param array $group Fields that define the grouping scope (e.g., ['userId'] for per-user exclusivity)
     * @param array $filters Additional fixed filters to apply when canceling other entities (e.g., ['type' => 'shipping'])
     * @param array $ensureFilters Additional filters to apply when ensuring first entity has value (e.g., ['active' => true])
     */
    public function __construct(
        private mixed $value,
        private bool $cancelEscorted = true,
        private mixed $cancelValue = null,
        private array $group = [],
        private array $filters = [],
        private array $ensureFilters = []
    ) {
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    public function isCancelEscorted(): bool
    {
        return $this->cancelEscorted;
    }

    public function setCancelEscorted(bool $cancelEscorted): void
    {
        $this->cancelEscorted = $cancelEscorted;
    }

    public function getCancelValue(): mixed
    {
        return $this->cancelValue;
    }

    public function setCancelValue(mixed $cancelValue): void
    {
        $this->cancelValue = $cancelValue;
    }

    public function getGroup(): array
    {
        return $this->group;
    }

    public function setGroup(array $group): void
    {
        $this->group = $group;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function setFilters(array $filters): void
    {
        $this->filters = $filters;
    }

    public function getEnsureFilters(): array
    {
        return $this->ensureFilters;
    }

    public function setEnsureFilters(array $ensureFilters): void
    {
        $this->ensureFilters = $ensureFilters;
    }
}

