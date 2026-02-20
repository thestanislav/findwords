<?php

namespace ExprAs\Asset\MiddleWare;

use AssetManager\Service\AssetManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @author James Jervis - https://github.com/jerv13
 */
class AssetManagerMiddleware implements MiddlewareInterface
{
    /**
     * @var AssetManager
     */
    protected $assetManager;

    /**
     * @param AssetManager $assetManager
     */
    public function __construct(
        AssetManager $assetManager
    ) {
        $this->assetManager = $assetManager;
    }

    /**
     * __invoke
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable|null          $next
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->assetManager->resolvesToAssetPsr($request)) {
            return $handler->handle($request);
        }

        return $this->assetManager->setAssetOnResponsePsr();
    }

}
