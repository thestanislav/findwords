<?php

namespace ExprAs\Nutgram\Entity;

use Doctrine\DBAL\Types\Types as DoctrineTypes;
use ExprAs\Logger\Entity\AbstractLogEntity;
use ExprAs\Rest\Mappings\Queryable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Telegram/Nutgram Log Entity
 * 
 * Stores logs from Telegram bot operations, webhook events, and bot errors.
 */
#[ORM\Table(name: 'expras_nutgram_logs')]
#[ORM\Entity]
class TelegramLogEntity extends AbstractLogEntity
{
    /**
     * Telegram update ID if available
     */
    #[Queryable]
    #[ORM\Column(name: 'update_id', type: DoctrineTypes::BIGINT, nullable: true)]
    protected ?int $updateId = null;

    /**
     * Related Telegram chat
     */
    #[ORM\ManyToOne(targetEntity: DefaultChat::class)]
    #[ORM\JoinColumn(name: 'chat_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    protected ?Chat $chat = null;

    /**
     * Related Telegram user
     */
    #[ORM\ManyToOne(targetEntity: DefaultUser::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    protected ?User $user = null;

    /**
     * Bot command or handler name
     */
    #[Queryable]
    #[ORM\Column(type: DoctrineTypes::STRING, length: 128, nullable: true)]
    protected ?string $handler = null;

       /**
     * Update type (message, callback_query, etc.)
     */
    #[Queryable]
    #[ORM\Column(name: 'update_type', type: DoctrineTypes::STRING, length: 32, nullable: true)]
    protected ?string $updateType = null;

    /**
     * Full update JSON data
     */
    #[ORM\Column(type: DoctrineTypes::JSON, nullable: true, name: 'update_json')]
    protected ?array $update = null;

    public function getUpdateId(): ?int
    {
        return $this->updateId;
    }

    public function setUpdateId(?int $updateId): void
    {
        $this->updateId = $updateId;
    }

    public function getChat(): ?Chat
    {
        return $this->chat;
    }

    public function setChat(?Chat $chat): void
    {
        $this->chat = $chat;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getHandler(): ?string
    {
        return $this->handler;
    }

    public function setHandler(?string $handler): void
    {
        $this->handler = $handler;
    }
    
    public function getUpdateType(): ?string
    {
        return $this->updateType;
    }

    public function setUpdateType(?string $updateType): void
    {
        $this->updateType = $updateType;
    }

    public function getUpdate(): ?array
    {
        return $this->update;
    }

    public function setUpdate(?array $update): void
    {
        $this->update = $update;
    }
}

