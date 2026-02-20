<?php

namespace ExprAs\User\MezzioAuthentication;

use Laminas\Session\Container;
use Laminas\Session\ManagerInterface as Manager;

class SessionContainer extends Container
{
    public function __construct($name = null, ?Manager $manager = null)
    {

        return parent::__construct($name ?? self::class, $manager);
    }
}
