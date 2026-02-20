<?php

namespace ExprAs\User\Entity;

use Doctrine\ORM\Mapping as ORM;
use ExprAs\Rest\Entity\AbstractEntity;
use ExprAs\Uploadable\Entity\Uploaded;

/**
 * Class UserProfile
 *
 * @package ExprAs\User\Entity
 */
#[ORM\Table(name: 'expras_user_profile')]
#[ORM\Entity]
class Profile extends AbstractEntity
{
    /**
     * @var ?string
     */
    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $url = null;

    /**
     * @var ?Uploaded
     */
    #[ORM\ManyToOne(targetEntity: \ExprAs\Uploadable\Entity\Uploaded::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    protected ?Uploaded $avatar = null;

    /**
     * Profile constructor.
     *
     * @param string|null $name
     * @param string|null $surname
     */
    public function __construct(
        #[ORM\Column(type: "string", nullable: true)]
        protected ?string $name = null,
        #[ORM\Column(type: "string", nullable: true)]
        protected ?string $surname = null
    ) {
    }


    /**
     * @return ?string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return ?string
     */
    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(?string $surname): void
    {
        $this->surname = $surname;
    }

    /**
     * @return Uploaded
     */
    public function getAvatar(): ?Uploaded
    {
        return $this->avatar;
    }

    public function setAvatar(?Uploaded $avatar): void
    {
        $this->avatar = $avatar;
    }

    /**
     * @return ?string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url = null): void
    {
        $this->url = $url;
    }


}
