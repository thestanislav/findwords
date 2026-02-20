<?php
/**
 * Author: Stanislav Anisimov <stanislav@ww9.ru>
 * Date: 21.08.13
 * Time: 20:53
 */

namespace ExprAs\Mailer\Service;

use Laminas\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{

    protected $sendLimit;

    /**
     * @param mixed $count
     */
    public function setSendLimit($count)
    {
        $this->sendLimit = $count;
    }

    /**
     * @return mixed
     */
    public function getSendLimit()
    {
        return $this->sendLimit;
    }

}

