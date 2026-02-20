<?php
/**
 * Author: Stanislav Anisimov <stanislav@ww9.ru>
 * Date: 21.08.13
 * Time: 20:12
 */

namespace ExprAs\Mailer\Service;


use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use ExprAs\Doctrine\Service\EntityManagerAwareTrait;
use ExprAs\Mailer\Entity\MailQueue;
use Symfony\Component\Mime\Email;

class MailQueueService
{
    use ServiceContainerAwareTrait;
    use EntityManagerAwareTrait;

    protected $_transportService;

    /**
     * @return \ExprAs\Mailer\Transport\MailQueue
     */
    public function getTransportService()
    {
        if (!$this->_transportService) {
            $this->_transportService = $this->getContainer()->get(\ExprAs\Mailer\Transport\MailQueue::class);
        }
        return $this->_transportService;
    }

    /**
     * @param Email $message
     * @param string $groupName
     *
     * @return \ExprAs\Mailer\Entity\MailQueue
     */
    public function queueMessage(Email $message, $groupName = 'default')
    {
        return $this->getTransportService()->send($message, $groupName);
    }

    /**
     * @param Email|null $message
     * @param string $groupName
     *
     * @return MailQueue
     */
    public function createQueueEntity(?Email $message = null, $groupName = 'default')
    {
        $entity = new MailQueue();
        if (!$message) {
            $message = new Email();
        }
        $entity->setMessage($message);
        $entity->setGroupName($groupName);
        return $entity;
    }

    /**
     * @param string $status
     * @param string|null $group
     * @param int|null $limit
     *
     * @return array
     */
    public function fetchMessages($status = MailQueue::STATUS_QUEUED, $group = null, $limit = null)
    {
        $repo = $this->getEntityManager()->getRepository(MailQueue::class);
        $criteria = array('status' => $status);
        if ($group) {
            $criteria['groupName'] = $group;
        }
        return $repo->findBy($criteria, array('ctime' => 'asc'), $limit);
    }

    /**
     * @param string|null $group
     * @param int|null $limit
     *
     * @return array
     */
    public function fetchQueuedMessages($group = null, $limit = null)
    {
        $criteria = array('status' => MailQueue::STATUS_QUEUED);

        $dql = sprintf('select e from %s e where e.scheduleTime < CURRENT_TIMESTAMP() and e.status = :status', MailQueue::class);
        if ($group) {
            $dql .= ' and e.groupName = :group';
            $criteria['groupName'] = $group;
        }
        $dql .= ' order by e.ctime asc';

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameters($criteria);
        $query->setMaxResults($limit);

        return $query->getResult();
    }

    /**
     * @return \ExprAs\Mailer\Service\ServiceOptions
     *
     */
    public function getOptions()
    {
        return $this->getContainer()->get(ServiceOptions::class);
    }

    public function processQueue($group = null, $limit = null)
    {
        $mailer = $this->getOptions()->getMailer();

        /** @var  $_queueMessages MailQueue[] */
        $_queueMessages = $this->fetchQueuedMessages($group, $limit);

        foreach ($_queueMessages as $_queueEntity) {
            try {
                $message = $_queueEntity->getMessage();
                $mailer->send($message);
                $_queueEntity->setStatus($_queueEntity::STATUS_SENT);
            } catch (\Throwable $ex) {
                $_queueEntity->setStatus($_queueEntity::STATUS_ERROR);
                $php_last_error = \error_get_last();
                $php_last_error_str = $php_last_error === null
                    ? 'null'
                    : \var_export($php_last_error, true);

                $_queueEntity->setLastError(\sprintf(
                    '%s (Error code:%s), Last error: %s',
                    $ex->getMessage(),
                    (string) $ex->getCode(),
                    $php_last_error_str
                ));
            }

            $this->getEntityManager()->persist($_queueEntity);
        }
        $this->getEntityManager()->flush();
    }
}

