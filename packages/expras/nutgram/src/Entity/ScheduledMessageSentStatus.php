<?php

namespace ExprAs\Nutgram\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use ExprAs\Doctrine\Behavior\Timestampable\TimestampableTrait;
use ExprAs\Rest\Entity\AbstractEntity;
use ExprAs\Rest\Mappings\Queryable;

#[Table(name: 'expras_nutgram_scheduled_message_sent_status')]
#[Entity]
class ScheduledMessageSentStatus extends AbstractEntity
{
    use TimestampableTrait;

    #[ManyToOne(targetEntity: DefaultUser::class)]
    #[JoinColumn(name: 'bot_user_id', referencedColumnName: 'id', nullable: false)]
    protected DefaultUser $botUser;

    #[ManyToOne(targetEntity: ScheduledMessage::class, inversedBy: 'sentStatuses')]
    #[JoinColumn(name: 'scheduled_message_id', referencedColumnName: 'id', nullable: false)]
    protected ScheduledMessage $scheduledMessage;

    #[Queryable]
    #[Column(name: 'sent_at', type: 'datetime')]
    protected \DateTime $sentAt;

    #[Queryable]
    #[Column(name: 'telegram_message_id', type: 'integer', nullable: true)]
    protected ?int $telegramMessageId = null;

    #[Queryable]
    #[Column(name: 'status_code', type: 'integer', nullable: true)]
    protected ?int $statusCode = null;

    #[Queryable]
    #[Column(name: 'status_text', type: 'text', nullable: true)]
    protected ?string $statusText = null;

    #[Queryable]
    #[Column(name: 'deleted', type: 'boolean')]
    protected bool $deleted = false;

    #[Queryable]
    #[Column(name: 'scheduled_to_delete', type: 'boolean')]
    protected bool $scheduledToDelete = false;

    #[Queryable]
    #[Column(name: 'scheduled_to_update', type: 'boolean')]
    protected bool $scheduledToUpdate = false;

    public function getBotUser(): DefaultUser
    {
        return $this->botUser;
    }

    public function setBotUser(DefaultUser $botUser): void
    {
        $this->botUser = $botUser;
    }

    public function getScheduledMessage(): ScheduledMessage
    {
        return $this->scheduledMessage;
    }

    public function setScheduledMessage(ScheduledMessage $scheduledMessage): void
    {
        $this->scheduledMessage = $scheduledMessage;
    }

    public function getSentAt(): \DateTime
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTime $sentAt): void
    {
        $this->sentAt = $sentAt;
    }

    public function getTelegramMessageId(): ?int
    {
        return $this->telegramMessageId;
    }

    public function setTelegramMessageId(?int $telegramMessageId): void
    {
        $this->telegramMessageId = $telegramMessageId;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function setStatusCode(?int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function getStatusText(): ?string
    {
        return $this->statusText;
    }

    public function setStatusText(?string $statusText): void
    {
        $this->statusText = $statusText;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }

    public function isScheduledToDelete(): bool
    {
        return $this->scheduledToDelete;
    }

    public function setScheduledToDelete(bool $scheduledToDelete): void
    {
        $this->scheduledToDelete = $scheduledToDelete;
    }

    public function isScheduledToUpdate(): bool
    {
        return $this->scheduledToUpdate;
    }

    public function setScheduledToUpdate(bool $scheduledToUpdate): void
    {
        $this->scheduledToUpdate = $scheduledToUpdate;
    }
}