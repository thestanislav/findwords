<?php

namespace ExprAs\Doctrine\Behavior\Aggregatable\Mapping\Driver;

use ExprAs\Doctrine\Behavior\Aggregatable\Mapping\Annotation\Aggregatable;
use ExprAs\Doctrine\Behavior\Aggregatable\Mapping\Annotation\AggregatableCriteria;
use Gedmo\Mapping\Driver\AbstractAnnotationDriver;

class Annotation extends AbstractAnnotationDriver
{
    public function readExtendedMetadata($meta, array &$config)
    {

        foreach ($meta->getReflectionClass()->getProperties() as $property) {
            if (($annot = $this->reader->getPropertyAnnotation($property, Aggregatable::class))) {

                $annot->setMapping($meta->getAssociationMapping($annot->getCollection()));

                if (($criteriaAnnot = $this->reader->getPropertyAnnotation($property, AggregatableCriteria::class))) {
                    $annot->setCriteria($criteriaAnnot);
                }

                $config[$property->getName()] = $annot;

            }
        }
    }
}
