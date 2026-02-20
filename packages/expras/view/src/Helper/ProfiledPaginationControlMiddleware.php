<?php
/**
 * Created by JetBrains PhpStorm.
 * User: stas
 * Date: 25.01.13
 * Time: 1:46
 * To change this template use File | Settings | File Templates.
 */

namespace ExprAs\View\Helper;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\View\Helper\AbstractHelper;
use Laminas\View\HelperPluginManager;

class ProfiledPaginationControlMiddleware implements MiddlewareInterface
{
    protected $_container;

    /**
     * ProfiledPaginationControlMiddleware constructor.
     *
     * @param $_container
     */
    public function __construct(ContainerInterface $_container)
    {
        $this->_container = $_container;
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $helper = $this->_container->get(HelperPluginManager::class)->get(ProfiledPaginationControl::class);
        $helper->setRequest($request);
        return $handler->handle($request);
    }

}
