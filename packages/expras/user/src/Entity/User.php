<?php

namespace ExprAs\User\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ExprAs\User\Entity\Trait\TelegramUserProvider;

/**
 * Class User
 */
#[ORM\Table(name: 'expras_user')]
#[ORM\Entity]
class User extends UserSuper
{
   
    //use TelegramUserProvider;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: RememberMe::class, cascade: ["remove"], orphanRemoval: true)]
    protected Collection $rememberTokens;

    public function __construct()
    {
        $this->rememberTokens = new ArrayCollection();
        parent::__construct();
    }



    public function getRememberTokens(): Collection
    {
        return $this->rememberTokens;
    }

    public function setRememberTokens(Collection $rememberTokens): void
    {
        $this->rememberTokens = $rememberTokens;
    }


}
