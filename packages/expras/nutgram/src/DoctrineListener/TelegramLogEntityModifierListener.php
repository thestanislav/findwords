<?php

namespace ExprAs\Nutgram\DoctrineListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use ExprAs\Nutgram\Entity\TelegramLogEntity;

/**
 * Telegram Log Entity Modifier Listener
 * 
 * Dynamically configures the targetEntity for user and chat relations
 * in TelegramLogEntity based on the nutgram configuration.
 * 
 * This allows the log entity to reference the configured user/chat entities
 * instead of hardcoding DefaultUser/DefaultChat.
 */
class TelegramLogEntityModifierListener implements EventSubscriber
{
    use ServiceContainerAwareTrait;

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();

        // Only modify TelegramLogEntity
        if ($classMetadata->getName() !== TelegramLogEntity::class) {
            return;
        }

        $config = $this->getContainer()->get('config')['nutgram'] ?? [];

        // Update user association to use configured userEntity
        if ($classMetadata->hasAssociation('user')) {
            $targetEntity = $config['userEntity'] ?? null;
            if ($targetEntity) {
                $mapping = $classMetadata->getAssociationMapping('user');
                if ($mapping['targetEntity'] !== $targetEntity) {
                    $classMetadata->associationMappings['user']['targetEntity'] = $targetEntity;
                }
            }
        }

        // Update chat association to use configured chatEntity
        if ($classMetadata->hasAssociation('chat')) {
            $targetEntity = $config['chatEntity'] ?? null;
            if ($targetEntity) {
                $mapping = $classMetadata->getAssociationMapping('chat');
                if ($mapping['targetEntity'] !== $targetEntity) {
                    $classMetadata->associationMappings['chat']['targetEntity'] = $targetEntity;
                }
            }
        }
    }

    public function getSubscribedEvents(): array
    {
        return ['loadClassMetadata'];
    }
}

