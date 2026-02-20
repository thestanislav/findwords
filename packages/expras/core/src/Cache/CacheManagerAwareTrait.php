<?php
/**
 * Created by JetBrains PhpStorm.
 * User: stas
 * Date: 23.12.12
 * Time: 21:19
 * To change this template use File | Settings | File Templates.
 */

namespace ExprAs\Core\Cache;

use Laminas\Cache\Storage\StorageInterface;

trait CacheManagerAwareTrait
{
    /**
     * @var StorageInterface
     */
    protected $_cacheManager = null;

    public function setCacheManager(StorageInterface $cacheStorage)
    {
        $this->_cacheManager = $cacheStorage;
    }

    /**
     * @return null|\Laminas\Cache\Storage\StorageInterface
     */
    public function getCacheManager()
    {
        return $this->_cacheManager;
    }
}
