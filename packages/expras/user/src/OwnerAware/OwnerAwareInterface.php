<?php
/**
 * Author: Stanislav Anisimov <stanislav@ww9.ru>
 * Date: 05.04.13
 * Time: 19:47
 */

namespace ExprAs\User\OwnerAware;

use ExprAs\User\Entity\UserSuper;

interface OwnerAwareInterface
{
    public function setOwner(UserSuper $user);

    public function getOwner();

    public function hasOwner();
}
