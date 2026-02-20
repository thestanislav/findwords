<?php

namespace ExprAs\User\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class RememberMe
 */
#[ORM\Table(name: 'expras_user_remember_me')]
#[ORM\UniqueConstraint(name: 'remember_idx', columns: ['sid', 'token', 'user_id'])]
#[ORM\Entity]
class RememberMe extends RememberMeSuper
{
    #[ORM\ManyToOne(targetEntity: \ExprAs\User\Entity\User::class, inversedBy: "rememberTokens")]
    protected UserSuper $user;

    public function setUser(UserSuper $user)
    {
        $this->user = $user;
    }

    /**
     * @return UserSuper
     */
    public function getUser(): UserSuper
    {
        return $this->user;
    }

}
