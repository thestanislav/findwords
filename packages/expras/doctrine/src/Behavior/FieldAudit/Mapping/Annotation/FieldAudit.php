<?php

namespace ExprAs\Doctrine\Behavior\FieldAudit\Mapping\Annotation;

use Gedmo\Mapping\Annotation\Annotation as GedmoAnnotation;

/**
 * @DoctrineAnnotation\NamedArgumentConstructor
 * @DoctrineAnnotation\Target("PROPERTY")
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class FieldAudit implements GedmoAnnotation
{
    public function __construct(
        protected string $targetField
    ) {
    }


    public function getTargetField(): string
    {
        return $this->targetField;
    }





}
