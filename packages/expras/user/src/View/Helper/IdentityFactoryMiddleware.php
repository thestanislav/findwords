<?php

declare(strict_types=1);

namespace ExprAs\User\View\Helper;

use ExprAs\Core\Http\CurrentRequestHolder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Sets the current request in CurrentRequestHolder so the Identity view helper
 * (created by IdentityFactoryFactory) can resolve the user from the holder.
 * Does not replace or register services in the container.
 */
class IdentityFactoryMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly CurrentRequestHolder $currentRequestHolder
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->currentRequestHolder->set($request);
        return $handler->handle($request);
    }
}
