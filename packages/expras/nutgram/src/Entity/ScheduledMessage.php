<?php

namespace ExprAs\Nutgram\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use ExprAs\Doctrine\Behavior\Timestampable\TimestampableTrait;
use ExprAs\Rest\Entity\AbstractEntity;
use ExprAs\Rest\Mappings\Queryable;
use ExprAs\Uploadable\Entity\Uploaded;

#[Table(name: 'expras_nutgram_scheduled_messages')]
#[Entity]
class ScheduledMessage extends AbstractEntity
{
    use TimestampableTrait;

    #[Queryable]
    #[Column(name: 'content', type: 'text')]
    protected string $content;

    #[Queryable]
    #[Column(name: 'scheduled_time', type: 'datetime')]
    protected \DateTime $scheduledTime;

    #[Queryable]
    #[Column(name: 'scheduled_to_criteria', type: 'text', nullable: true)]
    protected ?string $scheduledToCriteria = null;

    #[ManyToMany(targetEntity: DefaultUser::class)]
    #[JoinTable(name: 'expras_nutgram_scheduled_messages_users')]
    protected Collection $scheduledToUsers;

    #[OneToMany(mappedBy: 'scheduledMessage', targetEntity: ScheduledMessageSentStatus::class)]
    protected Collection $sentStatuses;

    #[ManyToOne(targetEntity: Uploaded::class, cascade: ["persist", "remove"])]
    #[JoinColumn(name: 'attachment_id', referencedColumnName: 'id', nullable: true)]
    protected ?Uploaded $attachment = null;

    #[Queryable]
    #[Column(name: 'use_markdown', type: 'boolean')]
    protected bool $useMarkDown = false;

    #[Queryable]
    #[Column(name: 'button_text', type: 'string', length: 255, nullable: true)]
    protected ?string $buttonText = null;

    #[Queryable]
    #[Column(name: 'button_command', type: 'string', length: 255, nullable: true)]
    protected ?string $buttonCommand = null;

    public function __construct()
    {
        $this->scheduledToUsers = new ArrayCollection();
        $this->sentStatuses = new ArrayCollection();
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getScheduledTime(): \DateTime
    {
        return $this->scheduledTime;
    }

    public function setScheduledTime(\DateTime $scheduledTime): void
    {
        $this->scheduledTime = $scheduledTime;
    }

    public function getScheduledToCriteria(): ?string
    {
        return $this->scheduledToCriteria;
    }

    public function setScheduledToCriteria(?string $scheduledToCriteria): void
    {
        $this->scheduledToCriteria = $scheduledToCriteria;
    }

    public function getScheduledToUsers(): Collection
    {
        return $this->scheduledToUsers;
    }

    public function addScheduledToUsers($user): void
    {
        if (is_iterable($user)) {
            foreach ($user as $item) {
                $this->addScheduledToUsers($item);
            }
            return;
        }

        if ($user instanceof DefaultUser && !$this->scheduledToUsers->contains($user)) {
            $this->scheduledToUsers->add($user);
        }
    }

    public function removeScheduledToUsers($user): void
    {
        if (is_iterable($user)) {
            foreach ($user as $item) {
                $this->removeScheduledToUsers($item);
            }
            return;
        }

        if ($user instanceof DefaultUser) {
            $this->scheduledToUsers->removeElement($user);
        }
    }

    public function getSentStatuses(): Collection
    {
        return $this->sentStatuses;
    }

    public function addSentStatuses($status): void
    {
        if (is_iterable($status)) {
            foreach ($status as $item) {
                $this->addSentStatuses($item);
            }
            return;
        }

        if ($status instanceof ScheduledMessageSentStatus && !$this->sentStatuses->contains($status)) {
            $this->sentStatuses->add($status);
            $status->setScheduledMessage($this);
        }
    }

    public function removeSentStatuses($status): void
    {
        if (is_iterable($status)) {
            foreach ($status as $item) {
                $this->removeSentStatuses($item);
            }
            return;
        }

        if ($status instanceof ScheduledMessageSentStatus) {
            if ($this->sentStatuses->removeElement($status)) {
                if ($status->getScheduledMessage() === $this) {
                    $status->setScheduledMessage(null);
                }
            }
        }
    }

    public function getAttachment(): ?Uploaded
    {
        return $this->attachment;
    }

    public function setAttachment(?Uploaded $attachment): void
    {
        $this->attachment = $attachment;
    }

    public function isUseMarkDown(): bool
    {
        return $this->useMarkDown;
    }

    public function setUseMarkDown(bool $useMarkDown): void
    {
        $this->useMarkDown = $useMarkDown;
    }

    public function getButtonText(): ?string
    {
        return $this->buttonText;
    }

    public function setButtonText(?string $buttonText): void
    {
        $this->buttonText = $buttonText;
    }

    public function getButtonCommand(): ?string
    {
        return $this->buttonCommand;
    }

    public function setButtonCommand(?string $buttonCommand): void
    {
        $this->buttonCommand = $buttonCommand;
    }
}