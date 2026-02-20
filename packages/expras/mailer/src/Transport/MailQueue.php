<?php
/**
 * Author: Stanislav Anisimov <stanislav@ww9.ru>
 * Date: 21.08.13
 * Time: 19:46
 */

namespace ExprAs\Mailer\Transport;


use ExprAs\Doctrine\Service\EntityManagerAwareTrait;
use Symfony\Component\Mime\Email;
use ExprAs\Mailer\Entity\MailQueue as MailQueueEntity;

class MailQueue
{

    use EntityManagerAwareTrait;
    /**
     * @var MailQueueEntity
     */
    protected $_lastEntity;

    /**
     * @param Email $message
     * @param string $groupName
     *
     * @return MailQueueEntity
     */
    public function send(Email $message, $groupName = 'default')
    {
        $entity = new MailQueueEntity();
        $entity->setMessage($message);
        $entity->setGroupName($groupName);
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush($entity);

        return $this->_lastEntity = $entity;
    }

    /**
     * @return MailQueueEntity
     */
    public function getLastEntity()
    {
        return $this->_lastEntity;
    }
}

