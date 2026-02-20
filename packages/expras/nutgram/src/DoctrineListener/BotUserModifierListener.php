<?php

namespace ExprAs\Nutgram\DoctrineListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use ExprAs\Nutgram\Entity\MessageToUser;
use ExprAs\Nutgram\Entity\ScheduledMessage;
use ExprAs\Nutgram\Entity\ScheduledMessageSentStatus;
use ExprAs\Nutgram\Entity\UserMessage;

class BotUserModifierListener implements EventSubscriber
{
    use ServiceContainerAwareTrait;

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        // Example modification: Add a unique constraint to a table
        if ($classMetadata->getName() === ScheduledMessage::class) {
            if ($classMetadata->hasAssociation('scheduledToUsers')) {
                $targetEntity = $this->getContainer()->get('config')['nutgram']['userEntity'];

                $mapping = $classMetadata->getAssociationMapping('scheduledToUsers');
                if ($mapping['targetEntity'] !== $targetEntity) {
                    $classMetadata->associationMappings['scheduledToUsers']['targetEntity'] = $targetEntity;
                }
            }
        } elseif ($classMetadata->getName() === ScheduledMessageSentStatus::class) {
            if ($classMetadata->hasAssociation('botUser')) {
                $targetEntity = $this->getContainer()->get('config')['nutgram']['userEntity'];

                $mapping = $classMetadata->getAssociationMapping('botUser');
                if ($mapping['targetEntity'] !== $targetEntity) {
                    $classMetadata->associationMappings['botUser']['targetEntity'] = $targetEntity;
                }
            }
        } elseif ($classMetadata->getName() === UserMessage::class) {
            if ($classMetadata->hasAssociation('user')) {
                $targetEntity = $this->getContainer()->get('config')['nutgram']['userEntity'];

                $mapping = $classMetadata->getAssociationMapping('user');
                if ($mapping['targetEntity'] !== $targetEntity) {
                    $classMetadata->associationMappings['user']['targetEntity'] = $targetEntity;
                }
            }
        } elseif ($classMetadata->getName() === MessageToUser::class) {
            if ($classMetadata->hasAssociation('addressee')) {
                $targetEntity = $this->getContainer()->get('config')['nutgram']['userEntity'];

                $mapping = $classMetadata->getAssociationMapping('addressee');
                if ($mapping['targetEntity'] !== $targetEntity) {
                    $classMetadata->associationMappings['addressee']['targetEntity'] = $targetEntity;
                }
            }
        }
    }

    public function getSubscribedEvents()
    {
        return ['loadClassMetadata'];
    }
}
