<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 19.07.2018
 * Time: 22:40
 */

namespace App\Middleware;


use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\I18n\View\Helper\Plural;
use Laminas\View\HelperPluginManager;

class PluralRuleSetter implements MiddlewareInterface
{
    use ServiceContainerAwareTrait;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        $pm = $this->getContainer()->get(HelperPluginManager::class);
        /** @var Plural $pluralHelper */
        $pluralHelper = $pm->get(Plural::class);
        $pluralHelper->setPluralRule('nplurals=2; plural=n == 1 ? 0 : 1');

        return $delegate->handle($request);
    }
}