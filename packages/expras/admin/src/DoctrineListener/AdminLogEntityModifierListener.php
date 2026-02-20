<?php

namespace ExprAs\Admin\DoctrineListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use ExprAs\Admin\Entity\AdminRequestLogEntity;

/**
 * Admin Log Entity Modifier Listener
 * 
 * Dynamically configures the targetEntity for user relation
 * in AdminRequestLogEntity based on the application configuration.
 * 
 * This allows the log entity to reference the configured user entity
 * instead of hardcoding UserSuper.
 */
class AdminLogEntityModifierListener implements EventSubscriber
{
    use ServiceContainerAwareTrait;

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();

        // Guard clause: Skip all entities except AdminRequestLogEntity
        // Using fully qualified class name for comparison
        $entityName = $classMetadata->getName();
        $adminRequestLogEntityName = AdminRequestLogEntity::class;
        
        if ($entityName !== $adminRequestLogEntityName) {
            return;  // Not our entity, skip it
        }

        // If we reach here, we're processing AdminRequestLogEntity
        $config = $this->getContainer()->get('config');
        
        // Get configured user entity (from admin or user config)
        $userEntity = $config['exprass_admin']['userEntity'] 
            ?? $config['expras-user']['entity_name'] 
            ?? \ExprAs\User\Entity\User::class;

        // Update user association to use configured userEntity
        if ($classMetadata->hasAssociation('user')) {
            $mapping = $classMetadata->getAssociationMapping('user');
            if ($mapping['targetEntity'] !== $userEntity) {
                $classMetadata->associationMappings['user']['targetEntity'] = $userEntity;
            }
        }
    }

    public function getSubscribedEvents(): array
    {
        return ['loadClassMetadata'];
    }
}

