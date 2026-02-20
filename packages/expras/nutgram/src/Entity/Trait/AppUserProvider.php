<?php

namespace ExprAs\Nutgram\Entity\Trait;

use ExprAs\User\Entity\UserSuper;

/**
 * Trait providing App User relationship mapping
 *
 * Entities using this trait will have their App User associations
 * automatically configured by the TelegramUserModifierListener when
 * Telegram authentication is enabled.
 *
 * Note: Doctrine mapping is added dynamically by TelegramUserModifierListener
 * to avoid hardcoding the target entity class. The actual application User entity
 * class is configured via 'expras-user.entity_name' config (defaults to User).
 *
 * Type hints use the base UserSuper class (MappedSuperclass) which all application
 * User entities extend, allowing configuration flexibility.
 */
trait AppUserProvider
{
    /**
     * One-to-one relationship with App User
     * Mapping is added dynamically by TelegramUserModifierListener
     * 
     * @var UserSuper|null The application User entity (class configured via 'expras-user.entity_name')
     */
    protected ?UserSuper $user = null;

    /**
     * Get associated App User
     * 
     * @return UserSuper|null The application User entity (actual class depends on 'expras-user.entity_name' config)
     */
    public function getUser(): ?UserSuper
    {
        return $this->user;
    }

    /**
     * Set associated App User
     * 
     * @param UserSuper|null $user The application User entity (actual class depends on 'expras-user.entity_name' config)
     */
    public function setUser(?UserSuper $user): void
    {
        $this->user = $user;
    }
}
