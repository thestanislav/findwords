<?php

namespace ExprAs\User\DoctrineListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use ExprAs\User\Entity\RememberMe;
use Psr\Container\ContainerInterface;

/**
 * RememberMe User Modifier Listener
 *
 * Dynamically sets the targetEntity for RememberMe's user association
 * based on the configured user entity class from 'expras-user' configuration.
 */
class RememberMeUserModifierListener implements EventSubscriber
{
    private ?ContainerInterface $container = null;

    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();
        $className = $classMetadata->getName();

        // Only process RememberMe entity
        if ($className !== RememberMe::class) {
            return;
        }

        // Get the configured user entity class
        $userEntityClass = $this->getUserEntityClass($eventArgs);
        if (!$userEntityClass) {
            // Fallback to default if config not available
            return;
        }

        // Update the user association's targetEntity if it exists
        if ($classMetadata->hasAssociation('user')) {
            // Get the existing association mapping
            $association = $classMetadata->getAssociationMapping('user');
            
            // Update only the targetEntity
            $association['targetEntity'] = $userEntityClass;
            
            // Remove the existing association
            unset($classMetadata->associationMappings['user']);
            
            // Remap with the updated targetEntity
            $classMetadata->mapManyToOne($association);
        }
    }

    /**
     * Get the configured user entity class from expras-user configuration
     */
    private function getUserEntityClass(LoadClassMetadataEventArgs $eventArgs): ?string
    {
        // Try to get from injected container first
        if ($this->container) {
            try {
                $config = $this->container->get('config');
                return $config['expras-user']['entity_name'] ?? null;
            } catch (\Throwable $e) {
                // Fall through to EntityManager approach
            }
        }

        // Fallback: try to get from EntityManager's metadata factory
        try {
            $entityManager = $eventArgs->getEntityManager();
            $metadataFactory = $entityManager->getMetadataFactory();
            
            // Try to access configuration through reflection if needed
            // This is a fallback - ideally container should be injected
            if ($metadataFactory instanceof \Doctrine\ORM\Mapping\ClassMetadataFactory) {
                // Try to get config from global container if available
                // This is a workaround for when listener is instantiated without container
                if (function_exists('app') || isset($GLOBALS['container'])) {
                    $container = $GLOBALS['container'] ?? null;
                    if ($container && $container instanceof \Psr\Container\ContainerInterface) {
                        $config = $container->get('config');
                        return $config['expras-user']['entity_name'] ?? null;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore errors
        }

        return null;
    }

    public function getSubscribedEvents()
    {
        return ['loadClassMetadata'];
    }
}

