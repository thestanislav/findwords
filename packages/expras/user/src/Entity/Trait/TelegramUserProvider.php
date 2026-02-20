<?php

namespace ExprAs\User\Entity\Trait;

use ExprAs\Nutgram\Entity\User as TelegramUserBase;

/**
 * Trait providing Telegram user relationship mapping
 *
 * Entities using this trait will have their Telegram user associations
 * automatically configured by the TelegramUserModifierListener when
 * Telegram authentication is enabled.
 *
 * Note: Doctrine mapping is added dynamically by TelegramUserModifierListener
 * to avoid hardcoding the target entity class. The actual Telegram user entity
 * class is configured via 'nutgram.userEntity' config (defaults to DefaultUser).
 *
 * Type hints use the base User class (MappedSuperclass) which all Telegram user
 * entities extend, allowing configuration flexibility.
 */
trait TelegramUserProvider
{
    /**
     * One-to-one relationship with Telegram user
     * Mapping is added dynamically by TelegramUserModifierListener
     * 
     * @var TelegramUserBase|null The Telegram user entity (class configured via 'nutgram.userEntity')
     */
    protected ?TelegramUserBase $telegramUser = null;

    /**
     * Get associated Telegram user
     * 
     * @return TelegramUserBase|null The Telegram user entity (actual class depends on 'nutgram.userEntity' config)
     */
    public function getTelegramUser(): ?TelegramUserBase
    {
        return $this->telegramUser;
    }

    /**
     * Set associated Telegram user
     * 
     * @param TelegramUserBase|null $telegramUser The Telegram user entity (actual class depends on 'nutgram.userEntity' config)
     */
    public function setTelegramUser(?TelegramUserBase $telegramUser): void
    {
        $this->telegramUser = $telegramUser;
    }
}
