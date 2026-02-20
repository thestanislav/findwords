<?php
/**
 * Created by JetBrains PhpStorm.
 * User: stas
 * Date: 25.01.13
 * Time: 1:10
 * To change this template use File | Settings | File Templates.
 */

namespace ExprAs\View\Helper;

use Mezzio\Router\RouteResult;
use Laminas\View\Helper\AbstractHelper;

class RouteMatch extends AbstractHelper
{
    protected ?RouteResult $routeResult = null;

    public function __construct(?RouteResult $routeResult = null)
    {
        $this->routeResult = $routeResult;
    }

    public function setRouteResult(RouteResult $routeResult): void
    {
        $this->routeResult = $routeResult;
    }

    /**
     * @return false|\Mezzio\Router\Route|null
     */
    public function __invoke()
    {
        return $this->routeResult?->getMatchedRoute();
    }
}
