<?php

namespace ExprAs\Nutgram\DoctrineListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use ExprAs\User\Entity\Trait\TelegramUserProvider;
use ExprAs\Nutgram\Entity\Trait\AppUserProvider;
use Psr\Container\ContainerInterface;
use ReflectionClass;

/**
 * Telegram User Modifier Listener
 *
 * Adds OneToOne associations to entities that use Telegram-related traits.
 * - TelegramUserProvider trait: adds telegramUser association (User → DefaultUser)
 * - AppUserProvider trait: adds user association (DefaultUser → User)
 * This provides a clean, trait-based approach to conditionally enabling Telegram user relationships.
 */
class TelegramUserModifierListener implements EventSubscriber
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

        // Handle TelegramUserProvider trait (User entities)
        if ($this->classUsesTrait($className, TelegramUserProvider::class)) {
            if (!$classMetadata->hasAssociation('telegramUser')) {
                // Get the configured Telegram user entity class from nutgram config
                $telegramUserEntityClass = $this->getTelegramUserEntityClass($eventArgs)
                    ?? \ExprAs\Nutgram\Entity\DefaultUser::class;
                
                $classMetadata->mapOneToOne([
                    'fieldName' => 'telegramUser',
                    'targetEntity' => $telegramUserEntityClass,
                    'mappedBy' => 'user',
                    'cascade' => ['persist'],
                ]);
            }
        }

        // Handle AppUserProvider trait (DefaultUser entities)
        if ($this->classUsesTrait($className, AppUserProvider::class)) {
            if (!$classMetadata->hasAssociation('user')) {
                // Get the configured user entity class from expras-user config
                $userEntityClass = $this->getUserEntityClass($eventArgs) 
                    ?? \ExprAs\User\Entity\User::class;
                
                $classMetadata->mapOneToOne([
                    'fieldName' => 'user',
                    'targetEntity' => $userEntityClass,
                    'inversedBy' => 'telegramUser',
                    'joinColumns' => [
                        [
                            'name' => 'user_id',
                            'referencedColumnName' => 'id',
                            'nullable' => true,
                            'onDelete' => 'SET NULL',
                        ]
                    ]
                ]);
            }
        }
    }

    /**
     * Check if a class uses a specific trait
     */
    private function classUsesTrait(string $className, string $traitName): bool
    {
        try {
            $reflectionClass = new ReflectionClass($className);

            // Check if the class directly uses the trait
            if (in_array($traitName, $reflectionClass->getTraitNames())) {
                return true;
            }

            // Check parent classes recursively
            while ($parentClass = $reflectionClass->getParentClass()) {
                $reflectionClass = $parentClass;
                if (in_array($traitName, $reflectionClass->getTraitNames())) {
                    return true;
                }
            }

            return false;
        } catch (\ReflectionException $e) {
            return false;
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

    /**
     * Get the configured Telegram user entity class from nutgram configuration
     */
    private function getTelegramUserEntityClass(LoadClassMetadataEventArgs $eventArgs): ?string
    {
        // Try to get from injected container first
        if ($this->container) {
            try {
                $config = $this->container->get('config');
                return $config['nutgram']['userEntity'] ?? null;
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
                        return $config['nutgram']['userEntity'] ?? null;
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
