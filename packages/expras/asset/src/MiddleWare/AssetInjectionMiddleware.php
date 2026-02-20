<?php

namespace ExprAs\Asset\MiddleWare;

use ExprAs\Asset\Service\RouteAssetInjector;
use Laminas\View\HelperPluginManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Router\RouteResult;

class AssetInjectionMiddleware implements MiddlewareInterface
{
    protected $assetInjector;
    protected $helperPluginManager;

    public function __construct(RouteAssetInjector $assetManager, HelperPluginManager $helperPluginManager)
    {
        $this->assetInjector = $assetManager;
        $this->helperPluginManager = $helperPluginManager;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $result = $request->getAttribute(RouteResult::class, false);

        if ($result instanceof RouteResult) {
            $this->assetInjector->configureAssets($result);
            $this->assetInjector->injectAssets($this->helperPluginManager);
        }


        return $handler->handle($request);
    }
}
