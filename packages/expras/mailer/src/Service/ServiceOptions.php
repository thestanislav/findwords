<?php
/**
 * Author: Stanislav Anisimov <stanislav@ww9.ru>
 * Date: 21.08.13
 * Time: 20:53
 */

namespace ExprAs\Mailer\Service;

use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use Laminas\Stdlib\AbstractOptions;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;

class ServiceOptions extends AbstractOptions
{

    use ServiceContainerAwareTrait;

    protected ?string $_dsn = null;

    protected ?TransportInterface $_transportObject = null;

    protected $_count;

    /**
     * @param string $dsn
     */
    public function setDsn(string $dsn): void
    {
        $this->_dsn = $dsn;
    }

    /**
     * @return string|null
     */
    public function getDsn(): ?string
    {
        return $this->_dsn;
    }

    /**
     * @param mixed $count
     */
    public function setCount($count)
    {
        $this->_count = $count;
    }

    /**
     * @return mixed
     */
    public function getCount()
    {
        return $this->_count;
    }

    /**
     * @return TransportInterface
     */
    public function getTransportObject(): TransportInterface
    {
        if (!$this->_transportObject) {
            $dsn = $this->getDsn() ?? 'native://default';
            $this->_transportObject = Transport::fromDsn($dsn);
        }
        return $this->_transportObject;
    }

    /**
     * Get a Mailer instance
     * @return MailerInterface
     */
    public function getMailer(): MailerInterface
    {
        return new Mailer($this->getTransportObject());
    }
}

