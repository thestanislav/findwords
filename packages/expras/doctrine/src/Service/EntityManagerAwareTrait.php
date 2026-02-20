<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 2/20/2018
 * Time: 16:27
 */

namespace ExprAs\Doctrine\Service;

use Doctrine\ORM\EntityManager;

trait EntityManagerAwareTrait
{
    protected $_entityManager;

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->_entityManager;
    }

    /**
     * @return $this
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->_entityManager = $entityManager;
        return $this;
    }


}
