<?php

namespace ExprAs\Doctrine\Behavior\Aggregatable\Mapping\Annotation;

use Gedmo\Mapping\Annotation\Annotation as GedmoAnnotation;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class AggregatableCriteria implements GedmoAnnotation
{
    public function __construct(private readonly string $field, private readonly string $operator, private readonly bool $isOr = false, private readonly array $args = [])
    {
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function isOr(): bool
    {
        return $this->isOr;
    }

}
