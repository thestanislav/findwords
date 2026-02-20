<?php

namespace ExprAs\Doctrine\Behavior\Ratable\Mapping\Driver;

use ExprAs\Doctrine\Behavior\Ratable\Mapping\Annotation\Ratable;
use Gedmo\Mapping\Driver\AbstractAnnotationDriver;

class Annotation extends AbstractAnnotationDriver
{
    public function readExtendedMetadata($meta, array &$config)
    {

        foreach ($meta->getReflectionClass()->getProperties() as $property) {
            if (($annot = $this->reader->getPropertyAnnotation($property, Ratable::class))) {
                $config[$property->getName()] = $annot;
            }
        }
    }
}
