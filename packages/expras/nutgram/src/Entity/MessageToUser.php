<?php

namespace ExprAs\Nutgram\Entity;


use Doctrine\ORM\Mapping as ORM;
use ExprAs\Doctrine\Behavior\Timestampable\TimestampableTrait;
use ExprAs\Rest\Entity\AbstractEntity;
use ExprAs\User\Entity\User;
use ExprAs\Nutgram\Entity\User as BotUser;
#[ORM\Entity]
#[ORM\Table(name: "expras_nutgram_messages_to_users")]
class MessageToUser extends AbstractEntity
{
    use TimestampableTrait;

    #[ORM\Column(type: "text", nullable: false)]
    protected string $text;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'sender_id', referencedColumnName: 'id', nullable: false)]
    protected User $sender;


    #[ORM\ManyToOne(targetEntity: DefaultUser::class)]
    #[ORM\JoinColumn(name: 'addressee_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected BotUser $addressee;

    #[ORM\Column(name: 'attachment_object', type: 'json', nullable: true)]
    protected ?array $attachmentObject = null;

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getSender(): User
    {
        return $this->sender;
    }

    public function setSender(User $sender): void
    {
        $this->sender = $sender;
    }

    public function getAddressee(): BotUser
    {
        return $this->addressee;
    }

    public function setAddressee(BotUser $addressee): void
    {
        $this->addressee = $addressee;
    }

    public function getAttachmentObject(): ?array
    {
        return $this->attachmentObject;
    }

    public function setAttachmentObject(?array $attachmentObject): void
    {
        $this->attachmentObject = $attachmentObject;
    }

}