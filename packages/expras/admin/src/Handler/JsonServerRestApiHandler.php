<?php

namespace ExprAs\Admin\Handler;

use ExprAs\Admin\Hydrator\Strategy\AssociationsStrategy;
use ExprAs\Admin\HydratorConfigurator\JsonApiFieldExcludeConfigurator;
use ExprAs\Admin\ResourceMapping\Configuration;
use ExprAs\Logger\ErrorListener\LoggingErrorListener;
use ExprAs\Rest\Handler\RestApiHandler;
use ExprAs\Rest\Hydrator\RestHydrator;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Hydrator\HydratorPluginManager;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Stdlib\Parameters;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JsonServerRestApiHandler extends RestApiHandler
{
    protected $config;


    /**
     * @return RestHydrator
     */
    #[\Override]
    public function getHydrator()
    {
        if (!$this->hydrator) {

            /**
             * @var HydratorPluginManager $hydratorManager 
             */
            $hydratorManager = $this->getContainer()->get(HydratorPluginManager::class);

            $this->hydrator = $hydratorManager->get(RestHydrator::class, $this->getContainer()->get('config')['exprass_admin']['hydrator_configurators']);
        }

        return $this->hydrator;
    }


    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        $this->config = new Parameters($this->getContainer()->get(Configuration::class)->findResourceConfig($request->getAttribute('resource')));
        $this->setEntityName($this->config->get('entity'));
        if (($excludeFields = $this->config->get('excludeFields'))) {
            $hydrator = $this->getHydrator();
            $hydrator->addConfigurator(
                new JsonApiFieldExcludeConfigurator(
                    $this->getEntityName(),
                    array_filter(
                        $excludeFields,
                        fn($key) => is_int($key),
                        ARRAY_FILTER_USE_KEY
                    )
                )
            );

            $action = $request->getAttribute('action');
            if (isset($excludeFields[$action]) && ArrayUtils::isList($excludeFields[$action])) {
                $hydrator->addConfigurator(
                    new JsonApiFieldExcludeConfigurator(
                        $this->getEntityName(),
                        $excludeFields[$action]
                    )
                );
            }
        }

        if (
            $request->hasHeader('x-meta') && ($meta = json_decode($request->getHeader('x-meta')[0], null, 512, JSON_THROW_ON_ERROR))
        ) {
            if (isset($meta->extractRelations)) {
                $hydrator = $this->getHydrator();
                foreach ($meta->extractRelations as $_relation) {
                    $hydrator->addStrategy($_relation, $strategy = new AssociationsStrategy());
                    $strategy->setHydrator($hydrator);
                    $strategy->setExtractRecursive(true);
                }
            }
            if (isset($meta->excludeFields)) {
                $hydrator = $this->getHydrator();
                $hydrator->addConfigurator(new JsonApiFieldExcludeConfigurator($this->getEntityName(), $meta->excludeFields));
            }
        }

        try {

            return parent::process($request, $handler);
        } catch (\Throwable $e) {

            $response = new JsonResponse(
                [
                    'message' => $e->getMessage(),
                    'line'    => $e->getLine(),
                    'file'    => $e->getFile(),
                    'trace'   => $e->getTrace(),
                ],
                500
            );

            try {
                $container = $this->getContainer();
                if ($container->has('expras_logger')) {
                    $logger = $container->get('expras_logger');
                    $logger->error($e->getMessage(), [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTrace(),
                    ], [
                        'exception' => $e,
                        'response' => $response,
                    ]);
                }
            } catch (\Throwable $ex) {
                $response = new JsonResponse(
                    [
                        'previous' => [
                            'message' => $e->getMessage(),
                            'line'    => $e->getLine(),
                            'file'    => $e->getFile(),
                            'trace'   => $e->getTrace(),
                        ],
                        'current' => [
                            'message' => $ex->getMessage(),
                            'line'    => $ex->getLine(),
                            'file'    => $ex->getFile(),
                            'trace'   => $ex->getTrace(),
                        ],
                    ],
                    500
                );
            }

            return $response;
        }
    }
}
