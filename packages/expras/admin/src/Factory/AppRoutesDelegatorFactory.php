<?php

/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/10/2017
 * Time: 16:20
 */

namespace ExprAs\Admin\Factory;

use ExprAs\Admin\Handler\JsonServerRestApiHandlerAbstractFactory;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Stdlib\SplPriorityQueue;
use Mezzio\Exception\InvalidArgumentException;
use Mezzio\Router\Route;
use Psr\Container\ContainerInterface;
use Mezzio\Application;

class AppRoutesDelegatorFactory
{
    /**
     * @param $serviceName
     *
     * @return  \Mezzio\Application
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        /**
         * @var Application  $app 
         */
        $app = $callback();

        $config = $container->get('config');
        if (isset($config['exprass_admin'])) {
            $this->injectRoutesFromConfig($app, $config['exprass_admin']);
        }

        return $app;
    }

    protected function _sortMiddleware($middlewares)
    {
        if (!is_array($middlewares) || count($middlewares) < 2) {
            return $middlewares;
        }
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
                $queue->insert($item['middleware'], [$priority, $serial]);
                $serial -= 1;
                return $queue;
            };
        };

        return array_reduce(
            $middlewares,
            $createPriorityQueueReducer(),
            new SplPriorityQueue()
        )->toArray();
    }

    public function injectRoutesFromConfig(Application $application, array $config): void
    {
        if (empty($config['routes']) || !is_array($config['routes'])) {
            return;
        }

        $basePath = $config['basePath'];

        $routes = ArrayUtils::merge($this->generateResourceMappingRouteConfig($application, $config), $config['routes']);

        foreach ($routes as $key => $spec) {
            if (!isset($spec['path']) || !isset($spec['middleware'])) {
                continue;
            }

            $methods = Route::HTTP_METHOD_ANY;
            if (isset($spec['allowed_methods'])) {
                $methods = $spec['allowed_methods'];
                if (!is_array($methods)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Allowed HTTP methods for a route must be in form of an array; received "%s"',
                            gettype($methods)
                        )
                    );
                }
            }

            $name = $spec['name'] ?? (is_string($key) ? $key : null);


            $route = $application->route(
                $basePath . $spec['path'],
                $this->_sortMiddleware($spec['middleware']),
                $methods,
                $name
            );

            if (isset($spec['options'])) {
                $options = $spec['options'];
                if (!is_array($options)) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Route options must be an array; received "%s"',
                            gettype($options)
                        )
                    );
                }

                $route->setOptions($options);
            }
        }
    }


    public function generateResourceMappingRouteConfig(Application $application, array $config)
    {
        if (!isset($config['resource_mappings'])) {
            return [];
        }


        $routes = [];
        foreach ($config['resource_mappings'] as $_k => $_data) {
            $_resource = $_data['name'] ?? $_k;
            $_middleware = $_data['middleware'] ?? (JsonServerRestApiHandlerAbstractFactory::SERVICE_PREFIX . $_resource);
            $routes
                = array_merge(
                    $routes,
                    [
                        'exprass-admin-get-list-' . $_resource => [
                            'path'            => '/' . $_resource,
                            'middleware'      => [
                                $_middleware
                            ],
                            'allowed_methods' => ['GET'],
                            'options'         => [
                                'defaults' => [
                                    'action'   => 'getList',
                                    'resource' => $_resource
                                ]
                            ]
                        ],
                        'exprass-admin-get-one-' . $_resource  => [
                            'path'            => '/' . $_resource . '/{entity_id:\d+}',
                            'middleware'      => [
                                $_middleware
                            ],
                            'allowed_methods' => ['GET'],
                            'options'         => [
                                'defaults' => [
                                    'action'   => 'getOne',
                                    'resource' => $_resource
                                ]
                            ]
                        ],
                        'exprass-admin-create-' . $_resource   => [
                            'path'            => '/' . $_resource,
                            'middleware'      => [
                                $_middleware
                            ],
                            'allowed_methods' => ['POST'],
                            'options'         => [
                                'defaults' => [
                                    'action'   => 'create',
                                    'resource' => $_resource
                                ]
                            ]
                        ],
                        'exprass-admin-update-' . $_resource   => [
                            'path'            => '/' . $_resource . '/{entity_id:\d+}',
                            'middleware'      => [
                                $_middleware
                            ],
                            'allowed_methods' => ['PUT'],
                            'options'         => [
                                'defaults' => [
                                    'action'   => 'update',
                                    'resource' => $_resource
                                ]
                            ]
                        ],
                        'exprass-admin-delete-' . $_resource   => [
                            'path'            => '/' . $_resource . '/{entity_id:\d+}',
                            'middleware'      => [
                                $_middleware
                            ],
                            'allowed_methods' => ['DELETE'],
                            'options'         => [
                                'defaults' => [
                                    'action'   => 'delete',
                                    'resource' => $_resource
                                ]
                            ]
                        ], //fallback action route
                        'exprass-admin-fallback-' . $_resource => [
                            'path'            => '/' . $_resource . '/{action:[\w\-]+}[/{entity_id:\d+}]',
                            'middleware'      => [
                                $_middleware
                            ],
                            'allowed_methods' => Route::HTTP_METHOD_ANY,
                            'options'         => [
                                'defaults' => [
                                    'resource' => $_resource
                                ]
                            ]
                        ]
                    ]
                );
        }

        return $routes;
    }
}
