<?php

namespace ExprAs\Rest\Hydrator\Configurator;

use Doctrine\DBAL\Types\Types as DoctrineTypes;
use Doctrine\ORM\Mapping\ClassMetadata;
use ExprAs\Rest\Hydrator\RestHydrator;
use ExprAs\Rest\Hydrator\Strategy\AssociationsStrategy;
use ExprAs\Rest\Hydrator\Strategy\DateTimeFormatterStrategy;

class DefaultHydratorConfigurator extends AbstractRestHydratorConfigurator
{
    protected array $_dateTimeFields = [];

    protected array $_associations = [];

    protected $_extractToOneFields = [];

    protected $_extractToManyFields = [];

    /**
     * @return array
     */
    public function getExtractToOneFields(): array
    {
        return $this->_extractToOneFields;
    }

    public function setExtractToOneFields(array $_extractToOneFields): void
    {
        $this->_extractToOneFields = $_extractToOneFields;
    }

    /**
     * @return array
     */
    public function getExtractToManyFields(): array
    {
        return $this->_extractToManyFields;
    }

    public function setExtractToManyFields(array $_extractToManyFields): void
    {
        $this->_extractToManyFields = $_extractToManyFields;
    }


    #[\Override]
    public function canConfigureStrategies(RestHydrator $hydrator, object $object): bool
    {

        $metadata = $hydrator->getObjectManager()->getClassMetadata($object::class);
        $this->_associations = $metadata->getAssociationNames();

        foreach ($metadata->getFieldNames() as $_name) {
            if (in_array(
                $metadata->getTypeOfField($_name), [
                DoctrineTypes::DATETIMETZ_MUTABLE,
                DoctrineTypes::DATETIMETZ_IMMUTABLE,
                DoctrineTypes::DATETIME_IMMUTABLE,
                DoctrineTypes::DATETIME_MUTABLE,
                DoctrineTypes::DATE_IMMUTABLE,
                DoctrineTypes::DATE_MUTABLE,
                DoctrineTypes::TIME_IMMUTABLE,
                DoctrineTypes::TIME_MUTABLE,

                ]
            )
            ) {
                $this->_dateTimeFields[$_name] = $metadata->getTypeOfField($_name);
            }
        }

        return $this->_dateTimeFields || $this->_associations;
    }

    public function configureStrategies(RestHydrator $hydrator, object $object): void
    {
        $mt = $hydrator->getObjectManager()->getClassMetadata($object::class);

        foreach ($this->_associations as $_association) {

            if ($hydrator->hasStrategy($_association)) {
                continue;
            }

            $target = $mt->getAssociationTargetClass($_association);
            $strategy = new AssociationsStrategy();
            $strategy->setHydrator($hydrator);
            $strategy->setCollectionName($_association);
            $strategy->setCollectionMetadata($hydrator->getObjectManager()->getClassMetadata($target));
            $strategy->setClassMetadata($mt);
            $strategy->setNullable($this->isNullable($mt, $_association));
            if ($mt->isCollectionValuedAssociation($_association) && in_array($_association, $this->_extractToManyFields)) {
                $strategy->setExtractToManyObjects(true);
            } elseif ($mt->isSingleValuedAssociation($_association) && in_array($_association, $this->_extractToOneFields)) {
                $strategy->setExtractToOneObjects(true);
            }


            $hydrator->addStrategy($_association, $strategy);
        }

        foreach ($this->_dateTimeFields as $_field => $_type) {


            $strategy = new DateTimeFormatterStrategy();
            if ($_type === DoctrineTypes::DATE_IMMUTABLE || $_type === DoctrineTypes::DATE_MUTABLE) {
                $strategy->setFormat('Y-m-d');
            } elseif ($_type === DoctrineTypes::TIME_MUTABLE || $_type === DoctrineTypes::TIME_IMMUTABLE) {
                $strategy->setFormat('H:i:s');
            } else {
                $strategy->setFormat('c');
            }
            $hydrator->addStrategy($_field, $strategy);
        }
    }


    protected function isNullable(ClassMetadata $metaData, $association): bool
    {

        if ($metaData->isSingleValuedAssociation($association) && method_exists($metaData, 'getAssociationMapping')) {
            $mapping = $metaData->getAssociationMapping($association);

            return false !== $mapping && isset($mapping['joinColumns']) && isset($mapping['joinColumns'][0]) && isset($mapping['joinColumns'][0]['nullable'])
                && $mapping['joinColumns'][0]['nullable'];
        }

        return false;
    }
}
