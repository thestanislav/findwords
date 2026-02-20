<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 28.07.2014
 * Time: 16:37
 */

namespace ExprAs\Asset\View\Helper;

use ExprAs\Core\View\Helper\AbstractHelper;
use Laminas\View\Exception\BadMethodCallException;

class Asset extends AbstractHelper
{
    protected $_assetManager = null;

    public function __invoke($src = null, $helper = null, $priority = 1)
    {
        if ($src) {
            $this->getAssetManager()->addAsset($src, $helper, $priority);
        }
        return $this;
    }

    /**
     * @return \ExprAs\Asset\Service\DefaultAssetManager
     */
    public function getAssetManager()
    {
        if (!$this->_assetManager) {
            $this->_assetManager = $this->getService('ExprAs\Asset\Service\DefaultAssetManager');
        }
        return $this->_assetManager;
    }


    public function __call($method, $args)
    {
        $manager = $this->getAssetManager();
        $method = 'add' . ucfirst((string) $method);
        if (is_callable([$manager, $method])) {
            return call_user_func_array([$manager, $method], $args);
        }

        throw new BadMethodCallException('Method "' . $method . '" does not exist');
    }
}
