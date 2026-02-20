<?php
/**
 * Created by JetBrains PhpStorm.
 * User: stas
 * Date: 27.11.12
 * Time: 13:18
 * To change this template use File | Settings | File Templates.
 */

namespace ExprAs\Core\PhpSettings;

use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Middleware implements MiddlewareInterface
{
    use ServiceContainerAwareTrait;
    /*
        * @param ServerRequestInterface $request
        * @param DelegateInterface $delegate
        * @return ResponseInterface
        * @throws \Psr\Container\ContainerExceptionInterface
        * @throws \Psr\Container\NotFoundExceptionInterface
        */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        $config = $this->getContainer()->get('config');
        if (isset($config['php_settings'])) {
            $this->getContainer()->get(SettingManager::class)->configSet($config['php_settings']);
        }

        return $delegate->handle($request);
    }
}
