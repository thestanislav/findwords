<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/10/2017
 * Time: 16:20
 */

namespace ExprAs\Core\ServiceManager\Factory;

use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\Stdlib\SplPriorityQueue;
use Psr\Container\ContainerInterface;
use Mezzio\Handler\NotFoundHandler;
use Mezzio\Application;
use Mezzio\Helper\ServerUrlMiddleware;
use Mezzio\Helper\UrlHelperMiddleware;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;
use Laminas\Stratigility\Middleware\ErrorHandler;
use Mezzio\Router\Middleware\ImplicitHeadMiddleware;
use Mezzio\Router\Middleware\ImplicitOptionsMiddleware;
use Mezzio\Router\Middleware\MethodNotAllowedMiddleware;
use Psr\Http\Server\MiddlewareInterface;

class PipelineAndRoutesDelegatorAppFactory
{
    /**
     * @param  ContainerInterface $container
     * @param  $serviceName
     * @param  callable           $callback
     * @return Application
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {

        /**
 * @var $app Application 
*/
        $app = $callback();
        $config = $container->get('config');
        $checkMiddlewareExistence = function ($requestName) use ($container) {
            if (is_string($requestName) && $container->has($requestName)) {
                return true;
            }

            if (is_array($requestName) && isset($requestName['middleware'])) {
                $middlewares = is_array($requestName['middleware']) ? $requestName['middleware'] : [$requestName['middleware']];
                return count(
                    array_filter(
                        $middlewares, fn($_middleware) => class_implements($_middleware)
                    )
                ) === count($middlewares);
            }

            throw new ServiceNotFoundException('Requested ' . $requestName . ' not found');
        };

        $createPriorityQueueReducer = function (): callable {
            // $serial is used to ensure that items of the same priority are enqueued
            // in the order in which they are inserted.
            $serial = PHP_INT_MAX;
            return function ($queue, $item) use (&$serial) {
                if (is_string($item)) {
                    $item = ['middleware' => $item];
                }
                $priority = isset($item['priority']) && is_int($item['priority'])
                    ? $item['priority']
                    : 1;
                $queue->insert($item, [$priority, $serial]);
                $serial -= 1;
                return $queue;
            };
        };

        // The error handler should be the first (most outer) middleware to catch
        // all Exceptions.
        //$app->pipe(ErrorHandler::class); moved to config
        $app->pipe(ServerUrlMiddleware::class);


        if (isset($config['pre_pipe_routing_middleware']) && ($middleware = $config['pre_pipe_routing_middleware'])) {
            $queue = array_reduce(
                array_filter($middleware, $checkMiddlewareExistence),
                $createPriorityQueueReducer(),
                new SplPriorityQueue()
            );
            foreach ($queue as $spec) {
                $path = $spec['path'] ?? '/';
                $app->pipe($path, $spec['middleware']);
            }
        }

        // Pipe more middleware here that you want to execute on every request:
        // - bootstrapping
        // - pre-conditions
        // - modifications to outgoing responses
        //
        // Piped Middleware may be either callables or service names. Middleware may
        // also be passed as an array; each item in the array must resolve to
        // middleware eventually (i.e., callable or service name).
        //
        // Middleware can be attached to specific paths, allowing you to mix and match
        // applications under a common domain.  The handlers in each middleware
        // attached this way will see a URI with the MATCHED PATH SEGMENT REMOVED!!!
        //
        // - $app->pipe('/api', $apiMiddleware);
        // - $app->pipe('/docs', $apiDocMiddleware);
        // - $app->pipe('/files', $filesMiddleware);

        $app->pipe(RouteMiddleware::class);

        if (isset($config['post_pipe_routing_middleware']) && ($middleware = $config['post_pipe_routing_middleware'])) {
            $queue = array_reduce(
                array_filter($middleware, $checkMiddlewareExistence),
                $createPriorityQueueReducer(),
                new SplPriorityQueue()
            );
            foreach ($queue as $spec) {
                $path = $spec['path'] ?? '/';
                $app->pipe($path, $spec['middleware']);
            }
        }

        // The following handle routing failures for common conditions:
        // - HEAD request but no routes answer that method
        // - OPTIONS request but no routes answer that method
        // - method not allowed
        // Order here matters; the MethodNotAllowedMiddleware should be placed
        // after the Implicit*Middleware.
        $app->pipe(ImplicitHeadMiddleware::class);
        $app->pipe(ImplicitOptionsMiddleware::class);
        $app->pipe(MethodNotAllowedMiddleware::class);

        // Seed the UrlHelper with the routing results:
        $app->pipe(UrlHelperMiddleware::class);

        if (isset($config['pre_pipe_dispatch_middleware']) && ($middleware = $config['pre_pipe_dispatch_middleware'])) {
            $queue = array_reduce(
                array_filter($middleware, $checkMiddlewareExistence),
                $createPriorityQueueReducer(),
                new SplPriorityQueue()
            );
            foreach ($queue as $spec) {
                $path = $spec['path'] ?? '/';
                $app->pipe($path, $spec['middleware']);
            }
        }
        // Add more middleware here that needs to introspect the routing results; this
        // might include:
        //
        // - route-based authentication
        // - route-based validation
        // - etc.

        // Register the dispatch middleware in the middleware pipeline
        $app->pipe(DispatchMiddleware::class);


        if (isset($config['post_pipe_dispatch_middleware']) && ($middleware = $config['post_pipe_dispatch_middleware'])) {
            $queue = array_reduce(
                array_filter($middleware, $checkMiddlewareExistence),
                $createPriorityQueueReducer(),
                new SplPriorityQueue()
            );
            foreach ($queue as $spec) {
                $path = $spec['path'] ?? '/';
                $app->pipe($path, $spec['middleware']);
            }
        }

        // At this point, if no Response is return by any middleware, the
        // NotFoundHandler kicks in; alternately, you can provide other fallback
        // middleware to execute.
        $app->pipe(NotFoundHandler::class);

        return $app;
    }


}
