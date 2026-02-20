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
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessages;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\View\HelperPluginManager;

class FlashFactory implements MiddlewareInterface
{
    use ServiceContainerAwareTrait;

    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $result = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE, false);
        if (!$result instanceof FlashMessages) {
            return $handler->handle($request);
        }
        /** @var HelperPluginManager $viewPlugins */
        $viewPlugins = $this->getContainer()->get(HelperPluginManager::class);
        $viewPlugins->get('flash')->setFlashMessages($result);

        return $handler->handle($request);
    }
}
