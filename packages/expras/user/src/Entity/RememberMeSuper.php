<?php
/**
 * Author: Stanislav Anisimov <stanislav@ww9.ru>
 * Date: 31.08.13
 * Time: 13:42
 */

namespace ExprAs\User\Entity;

use Doctrine\ORM\Mapping as ORM;
use ExprAs\Rest\Entity\AbstractEntity;

/**
 * Class RememberMeSuper
 */
#[ORM\MappedSuperclass]
class RememberMeSuper extends AbstractEntity
{
    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 16)]
    protected string $sid;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 16)]
    protected string $token;

    /**
     * @var UserSuper
     */


    /**
     * @param string $sid
     */
    public function setSid($sid)
    {
        $this->sid = $sid;
    }

    /**
     * @return string
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }



}
