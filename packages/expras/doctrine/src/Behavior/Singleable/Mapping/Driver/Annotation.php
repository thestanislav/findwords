<?php

namespace ExprAs\Doctrine\Behavior\Singleable\Mapping\Driver;

use ExprAs\Doctrine\Behavior\Singleable\Mapping\Annotation\Singleable;
use Gedmo\Mapping\Driver\AbstractAnnotationDriver;

class Annotation extends AbstractAnnotationDriver
{
    public function readExtendedMetadata($meta, array &$config): void
    {
        $config['singleables'] = [];
        foreach ($meta->getReflectionClass()->getProperties() as $property) {
            if (($annot = $this->reader->getPropertyAnnotation($property, Singleable::class))) {
                $config['singleables'][$property->getName()] = $annot;
            }
        }
    }
}

