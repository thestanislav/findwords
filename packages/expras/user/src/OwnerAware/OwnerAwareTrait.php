<?php
/**
 * Author: Stanislav Anisimov <stanislav@ww9.ru>
 * Date: 05.04.13
 * Time: 19:47
 */

namespace ExprAs\User\OwnerAware;

use ExprAs\User\Entity\User;
use ExprAs\User\Entity\UserSuper;
use Doctrine\ORM\Mapping as ORM;

trait OwnerAwareTrait
{
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    protected ?UserSuper $_owner = null;

    /**
     * @return $this
     */
    public function setOwner(?UserSuper $owner = null)
    {
        $this->_owner = $owner;
    }

    /**
     * @return ?UserSuper || null
     */
    public function getOwner(): ?UserSuper
    {
        return $this->_owner;
    }

    /**
     * @return bool
     */
    public function hasOwner(): bool
    {
        return ($this->getOwner() instanceof UserSuper);
    }
}
