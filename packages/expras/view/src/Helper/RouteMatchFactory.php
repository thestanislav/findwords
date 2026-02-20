<?php
/**
 * Created by JetBrains PhpStorm.
 * User: stas
 * Date: 25.01.13
 * Time: 1:10
 * To change this template use File | Settings | File Templates.
 */

namespace ExprAs\View\Helper;

use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Router\RouteResult;
use Laminas\View\HelperPluginManager;

class RouteMatchFactory implements MiddlewareInterface
{
    use ServiceContainerAwareTrait;

    /**
     * @param  ServerRequestInterface $request
     * @param  DelegateInterface      $handler
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $result = $request->getAttribute(RouteResult::class, false);
        if (!$result instanceof RouteResult) {
            return $handler->handle($request);
        }
        /** @var HelperPluginManager $viewPlugins */
        $viewPlugins = $this->getContainer()->get(HelperPluginManager::class);
        $viewPlugins->get('routeMatch')->setRouteResult($result);

        return $handler->handle($request);
    }
}
