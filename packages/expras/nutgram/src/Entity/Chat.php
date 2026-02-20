<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 8/24/2017
 * Time: 15:00
 */

namespace ExprAs\Nutgram\Entity;

use Doctrine\DBAL\Types\Types as DoctrineTypes;
use ExprAs\Doctrine\Behavior\Timestampable\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use ExprAs\Rest\Mappings\Queryable;

#[ORM\MappedSuperclass]
class Chat
{
    use TimestampableTrait;

    /**
     * @var integer
     */
    #[ORM\Id]
    #[ORM\Column(type: DoctrineTypes::BIGINT)]
    protected int $id;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'title', type: 'string', nullable: true)]
    protected ?string $title = null;


    #[ORM\Column(name: 'type', type: 'string', nullable: false)]
    protected string $type;

    /**
     * @var ?string
     */
    #[Queryable]
    #[ORM\Column(name: 'username', type: 'string', nullable: true)]
    protected ?string $username = null;


    /**
     * @var string
     */
    #[Queryable]
    #[ORM\Column(name: 'first_name', type: 'string', nullable: true)]
    protected ?string $firstName = null;

    /**
     * @var ?string
     */
    #[Queryable]
    #[ORM\Column(name: 'last_name', type: 'string', nullable: true)]
    protected ?string $lastName = null;


    #[ORM\Column(name: 'is_forum', type: 'boolean', nullable: false)]
    protected bool $isForum = false;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function isForum(): bool
    {
        return $this->isForum;
    }

    public function setIsForum(bool $isForum): void
    {
        $this->isForum = $isForum;
    }
}
