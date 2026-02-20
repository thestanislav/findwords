<?php

namespace ExprAs\Nutgram\Entity;


use ExprAs\Doctrine\Behavior\Timestampable\TimestampableTrait;

use ExprAs\Rest\Entity\AbstractEntity;
use ExprAs\Rest\Mappings\Queryable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'expras_nutgram_user_messages')]
#[ORM\Entity]
class UserMessage extends AbstractEntity
{
    use TimestampableTrait;

    #[ORM\ManyToOne(targetEntity: DefaultUser::class, inversedBy: "messages")]
    #[ORM\JoinColumn(name: "user_id", nullable: false)]
    protected User $user;

    #[Queryable]
    #[ORM\Column(name: 'update_type', type: 'string', nullable: true)]
    protected ?string $updateType = null;

    #[Queryable]
    #[ORM\Column(name: 'text_message', type: 'text', nullable: true)]
    protected ?string $textMessage = null;

    #[Queryable]
    #[ORM\Column(name: 'message_object', type: 'json', nullable: true)]
    protected ?array $messageObject = null;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getTextMessage(): ?string
    {
        return $this->textMessage;
    }

    public function setTextMessage(?string $textMessage): void
    {
        $this->textMessage = $textMessage;
    }

    public function getMessageObject()
    {
        return $this->messageObject;
    }

    public function setMessageObject($messageObject): void
    {
        $this->messageObject = $messageObject;
    }

    public function getUpdateType(): ?string
    {
        return $this->updateType;
    }

    public function setUpdateType(?string $updateType): void
    {
        $this->updateType = $updateType;
    }



}