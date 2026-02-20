<?php

namespace ExprAs\Doctrine\Behavior\FieldAudit\Mapping\Driver;

use ExprAs\Doctrine\Behavior\FieldAudit\Mapping\Annotation\FieldAudit;
use Gedmo\Mapping\Driver\AbstractAnnotationDriver;

class Annotation extends AbstractAnnotationDriver
{
    public function readExtendedMetadata($meta, array &$config)
    {

        $config['field_audit'] = [];
        foreach ($meta->getReflectionClass()->getProperties() as $property) {
            if (($annot = $this->reader->getPropertyAnnotation($property, FieldAudit::class))) {
                $config['field_audit'][$property->getName()] = $annot;
            }
        }

    }
}
