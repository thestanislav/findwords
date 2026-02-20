<?php

namespace ExprAs\Admin\Middleware;

use Laminas\Stratigility\EmptyPipelineHandler;
use Laminas\Stratigility\Middleware\CallableMiddlewareDecorator;
use Laminas\Stratigility\Middleware\DoublePassMiddlewareDecorator;
use Laminas\Stratigility\Middleware\RequestHandlerMiddleware;
use Laminas\Stratigility\MiddlewarePipe;
use Laminas\Stratigility\Next;
use Mezzio\Authentication\UserInterface;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Authentication\AuthenticationMiddleware;

use function Laminas\Stratigility\doublePassMiddleware;

class AdminAuthenticationMiddleware extends AuthenticationMiddleware
{
    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /**
 * @var $routeResult RouteResult 
*/
        if (!($routeResult = $request->getAttribute(RouteResult::class))) {
            return $handler->handle($request);
        }

        if ($routeResult->isFailure()) {
            return $handler->handle($request);
        }

        if (in_array(
            $routeResult->getMatchedRouteName(), [
            'exprass-admin',
            //'exprass-admin-resources',
            'exprass-admin-logout'
            ]
        )
        ) {
            return $handler->handle($request);
        }

        $queue = new \SplQueue();
        $queue->enqueue(new CallableMiddlewareDecorator([$this->auth, 'complete']));

        $handler = new Next($queue, $handler);
        ;


        if (null !== ($user = $this->auth->authenticate($request))) {
            return $handler->handle(
                $request
                    ->withAttribute(UserInterface::class, $user)
            );
        }

        if (in_array($routeResult->getMatchedRouteName(), ['exprass-admin-login'])) {
            return $handler->handle($request);
        }
        return $this->auth->unauthorizedResponse($request);
    }
}
