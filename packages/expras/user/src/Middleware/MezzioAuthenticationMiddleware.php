<?php

namespace ExprAs\User\Middleware;

use Laminas\Stratigility\Middleware\CallableMiddlewareDecorator;
use Laminas\Stratigility\MiddlewarePipe;
use Laminas\Stratigility\Next;
use Mezzio\Authentication\UserInterface;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Authentication\AuthenticationMiddleware;

class MezzioAuthenticationMiddleware extends AuthenticationMiddleware
{
    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouteResult $routeResult */
        if (! ($routeResult = $request->getAttribute(RouteResult::class)) || ! $routeResult->getMatchedRouteName()) {
            return $handler->handle($request);
        }

        if ($routeResult->isFailure()) {
            return $handler->handle($request);
        }

        $queue = new \SplQueue();
        $queue->enqueue(new CallableMiddlewareDecorator([$this->auth, 'complete']));
        $handler = new Next($queue, $handler);


        if (null !== ($user = $this->auth->authenticate($request))) {
            return $handler->handle(
                $request
                    ->withAttribute(UserInterface::class, $user)
            );
        }
        return $this->auth->unauthorizedResponse($request);
    }
}
