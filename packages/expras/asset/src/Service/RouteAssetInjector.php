<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 06.04.2014
 * Time: 14:33
 */

namespace ExprAs\Asset\Service;

use Mezzio\Router\RouteResult;

class RouteAssetInjector extends AbstractAssetInjector
{
    public function __construct(protected $_routes)
    {
    }


    /**
     * @throws \DomainException
     */
    public function configureAssets(RouteResult $routeResult)
    {
        $routeName = $routeResult->getMatchedRouteName();
        foreach ($this->_routes as $_match => $_assets) {
            if ($_match != $routeName
                && !preg_match('~' . $_match . '~i', $routeName)
            ) {
                continue;
            }

            foreach ($_assets as $_k => $_v) {
                $pr = $_k;
                if (is_array($_v) && isset($_v['priority'])) {
                    $pr = $_v['priority'];
                }
                $this->addAsset($_v, null, $pr);
            }
        }
    }

}
