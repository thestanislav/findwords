<?php

namespace ExprAs\Admin\HydratorConfigurator;

use ExprAs\Admin\Hydrator\Strategy\CollectionUploadedExtractor;
use ExprAs\Admin\Hydrator\Strategy\SingleUploadedExtractor;
use ExprAs\Rest\Hydrator\Configurator\AbstractRestHydratorConfigurator;
use ExprAs\Rest\Hydrator\RestHydrator;
use ExprAs\Uploadable\Entity\Uploaded;

class UploadedFieldConfigurator extends AbstractRestHydratorConfigurator
{
    protected array $_uploadedFields = [];

    #[\Override]
    public function canConfigureStrategies(RestHydrator $hydrator, object $object): bool
    {
        $md = $hydrator->getObjectManager()->getClassMetadata($object::class);
        $this->_uploadedFields = array_filter(
            $md->getAssociationNames(),
            fn ($name) => $md->getAssociationTargetClass($name) === Uploaded::class
        );

        return !!$this->_uploadedFields;
    }

    public function configureStrategies(RestHydrator $hydrator, object $object): void
    {
        $md = $hydrator->getObjectManager()->getClassMetadata($object::class);
        $collectionStrategy = $singleStrategy = null;
        foreach ($this->_uploadedFields as $_field) {
            if ($md->isCollectionValuedAssociation($_field)) {
                if (is_null($collectionStrategy)) {
                    $collectionStrategy = new CollectionUploadedExtractor();
                }
                $collectionStrategy->setCollectionName($_field);
                $collectionStrategy->setClassMetadata($md);
                $hydrator->addStrategy($_field, $collectionStrategy);

            } else {
                if (is_null($singleStrategy)) {
                    $singleStrategy = new SingleUploadedExtractor();
                }
                $hydrator->addStrategy($_field, $singleStrategy);
            }

        }
    }

}
