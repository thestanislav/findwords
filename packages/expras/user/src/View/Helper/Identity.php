<?php

namespace ExprAs\User\View\Helper;

use Doctrine\ORM\EntityManager;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use ExprAs\User\Entity\UserSuper;
use Laminas\View\Helper\AbstractHelper;
use Mezzio\Authentication\AuthenticationInterface;

class Identity extends AbstractHelper
{
    /**
     * @var callable
     */
    protected $_authCheck;

    public function __construct(callable $authCheck)
    {
        $this->_authCheck = $authCheck;
    }

    /**
     * @return UserSuper|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function __invoke()
    {
        return ($this->_authCheck)();
    }
}
