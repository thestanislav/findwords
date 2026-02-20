<?php

namespace ExprAs\Doctrine\Behavior\FieldAudit;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Proxy\Proxy;
use Gedmo\Mapping\Driver\AttributeReader;
use Gedmo\Mapping\MappedEventSubscriber;
use Doctrine\ORM\Events;

class FieldAuditListener extends MappedEventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
            Events::onFlush,
        ];
    }



    protected function getNamespace(): string
    {
        return __NAMESPACE__;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {

        $this->setAnnotationReader(new AttributeReader());
        $this->loadMetadataForObjectClass($eventArgs->getObjectManager(), $eventArgs->getClassMetadata());

    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $entityManager = $args->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $updatedEntities = $unitOfWork->getScheduledEntityUpdates();


        foreach ($updatedEntities as $updatedEntity) {

            if ($updatedEntity instanceof Proxy) {
                $entityName = get_parent_class($updatedEntity);
            } else {
                $entityName = $updatedEntity::class;
            }

            if (($config = $this->getConfiguration($entityManager, $entityName))) {

                $changeset = $unitOfWork->getEntityChangeSet($updatedEntity);

                foreach ($config['field_audit'] as $_field => $_config) {

                    if (array_key_exists($_config->getTargetField(), $changeset)) {

                        $changes = $changeset[$_config->getTargetField()];

                        $previousValueForField = array_key_exists(0, $changes) ? $changes[0] : null;
                        $newValueForField = array_key_exists(1, $changes) ? $changes[1] : null;

                        if ($previousValueForField != $newValueForField) {

                            $metaData = $entityManager->getClassMetadata($entityName);
                            $targetEntity = $metaData->getAssociationTargetClass($_field);

                            $assocTargetMetaData = $entityManager->getClassMetadata($targetEntity);
                            $auditEntity = new $targetEntity();
                            $auditEntity->setFieldValue($newValueForField);
                            $assocTargetMetaData->setFieldValue($auditEntity, $metaData->getAssociationMappedByTargetField($_field), $updatedEntity);
                            $entityManager->persist($auditEntity);


                            $unitOfWork->computeChangeSet($assocTargetMetaData, $auditEntity);
                        }
                    }
                }
            }


        }
    }

}
