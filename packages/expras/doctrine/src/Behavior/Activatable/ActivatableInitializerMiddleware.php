<?php
/**
 * Author: Stanislav Anisimov <stanislav@ww9.ru>
 * Date: 27.05.13
 * Time: 17:55
 */

namespace ExprAs\Doctrine\Behavior\Activatable;

use ExprAs\Doctrine\Service\EntityManagerAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ActivatableInitializerMiddleware implements MiddlewareInterface
{
    use EntityManagerAwareTrait;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->getEntityManager()->getFilters()->enable('activatable');

        return $handler->handle($request);
    }
}
