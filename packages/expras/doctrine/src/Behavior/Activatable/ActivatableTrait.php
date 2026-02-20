<?php
/**
 * Author: Stanislav Anisimov <stanislav@ww9.ru>
 * Date: 27.05.13
 * Time: 17:55
 */

namespace ExprAs\Doctrine\Behavior\Activatable;

use Doctrine\ORM\Mapping as ORM;

trait ActivatableTrait
{
    /**
     * @var boolean
     */
    #[ORM\Column(name: "is_active", type: 'boolean', nullable: false, options: ["default" => 0])]
    protected bool $isActive = false;

    /**
     * @param boolean $isActive
     */
    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * @return boolean
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }
}
