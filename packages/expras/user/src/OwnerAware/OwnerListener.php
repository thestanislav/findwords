<?php
/**
 * Author: Stanislav Anisimov <stanislav@ww9.ru>
 * Date: 05.04.13
 * Time: 19:47
 */

namespace ExprAs\User\OwnerAware;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use ExprAs\User\Entity\UserSuper;

class OwnerListener implements EventSubscriber
{
    protected $_user;

    /**
     * @return UserSuper|null
     */
    public function getUser(): ?UserSuper
    {
        return $this->_user;
    }

    /**
     * @param UserSuper|null $user
     */
    public function setUser(?UserSuper $user): void
    {
        $this->_user = $user;
    }



    /**
     * @see EventSubscriber
     */
    public function getSubscribedEvents()
    {
        return [Events::prePersist];
    }

    public function prePersist(LifecycleEventArgs $args)
    {

        if (($entity = $args->getEntity()) && $entity instanceof OwnerAwareInterface && $this->getUser()) {
            $user = $this->getUser();
            $args->getEntityManager()->persist($user);
            $entity->setOwner($user);
        }
    }

}
