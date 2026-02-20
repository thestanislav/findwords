<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 05.02.2015
 * Time: 17:25
 */

namespace ExprAs\Asset\Service;

use Assetic\Asset\AssetInterface;
use AssetManager\Resolver\ResolverInterface;
use AssetManager\Service\AssetManager as BaseAssetManager;
use Laminas\Stdlib\RequestInterface;
use Laminas\Http\PhpEnvironment\Request;
use AssetManager\Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;

class AssetManager extends BaseAssetManager
{
    /**
     * @param ResolverInterface $resolver
     */
    public function __construct(
        ResolverInterface $resolver,
        array $config
    ) {
        parent::__construct(
            $resolver,
            $config
        );
    }

    /**
     * Check if the request resolves to an asset.
     *
     * @return boolean
     */
    public function resolvesToAssetPsr(ServerRequestInterface $request)
    {
        if (null === $this->asset) {
            $this->asset = $this->resolvePsr($request);
        }

        return (bool)$this->asset;
    }

    /**
     * Set the asset on the response, including headers and content.
     *
     * @return ResponseInterface
     * @throws Exception\RuntimeException
     */
    public function setAssetOnResponsePsr()
    {
        if (!$this->asset instanceof AssetInterface) {
            throw new Exception\RuntimeException(
                'Unable to set asset on response. Request has not been resolved to an asset.'
            );
        }

        // @todo: Create Asset wrapper for mimetypes
        if (empty($this->asset->mimetype)) {
            throw new Exception\RuntimeException('Expected property "mimetype" on asset.');
        }

        $this->getAssetFilterManager()->setFilters($this->path, $this->asset);

        $this->asset = $this->getAssetCacheManager()->setCache($this->path, $this->asset);
        $mimeType = $this->asset->mimetype;
        $assetContents = $this->asset->dump();

        // @codeCoverageIgnoreStart
        if (function_exists('mb_strlen')) {
            $contentLength = mb_strlen((string) $assetContents, '8bit');
        } else {
            $contentLength = strlen((string) $assetContents);
        }
        // @codeCoverageIgnoreEnd

        if (!empty($this->config['clear_output_buffer']) && $this->config['clear_output_buffer']) {
            // Only clean the output buffer if it's turned on and something
            // has been buffered.
            if (ob_get_length() > 0) {
                ob_clean();
            }
        }

        $body = new Stream('php://temp', 'wb+');
        $body->write($assetContents);
        $body->rewind();

        $response = new Response(
            $body,
            200,
            [
                'Content-Transfer-Encoding' => 'binary',
                'Content-Type' => $mimeType,
                'Content-Length' => $contentLength
            ]
        );

        $this->assetSetOnResponse = true;

        return $response;
    }

    /**
     * Resolve the request to a file.
     *
     * @return mixed false when not found, AssetInterface when resolved.
     */
    protected function resolvePsr(ServerRequestInterface $request)
    {
        /* @var $uri \Laminas\Uri\UriInterface */
        $uri = $request->getUri();
        //$fullPath   = $uri->getPath();
        //$path       = substr($fullPath, strlen($request->getBasePath()) + 1);
        $path = $uri->getPath();
        $path = ltrim($path, '/');
        $this->path = $path;
        $asset = $this->getResolver()->resolve($path);

        if (!$asset instanceof AssetInterface) {
            return false;
        }

        return $asset;
    }

    /**
     * Resolve the request to a file.
     *
     * @return mixed false when not found, AssetInterface when resolved.
     */
    protected function resolve(RequestInterface $request)
    {
        if (!$request instanceof Request) {
            return false;
        }

        /* @var $request Request */
        /* @var $uri \Laminas\Uri\UriInterface */
        $uri = $request->getUri();
        $fullPath = $uri->getPath();
        $path = urldecode(substr((string) $fullPath, strlen($request->getBasePath()) + 1));
        $this->path = $path;
        $asset = $this->getResolver()->resolve($path);

        if (!$asset instanceof AssetInterface) {
            return false;
        }

        return $asset;
    }
}
