<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 12.04.2014
 * Time: 12:34
 */

namespace ExprAs\Asset\Asset;

use Assetic\Asset\BaseAsset;
use Assetic\Filter\FilterInterface;
use Laminas\Http\Client as HttpClient;

class HttpAsset extends BaseAsset
{
    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * Constructor.
     *
     * @param string  $sourceUrl    The source URL
     * @param array   $filters      An array of filters
     * @param Boolean $ignoreErrors
     *
     * @throws \InvalidArgumentException If the first argument is not an URL
     */
    public function __construct($sourceUrl, $filters = [], protected $ignoreErrors = false, array $vars = [])
    {
        if (str_starts_with($sourceUrl, '//')) {
            $sourceUrl = 'http:' . $sourceUrl;
        } elseif (!str_contains($sourceUrl, '://')) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid URL.', $sourceUrl));
        }

        $this->httpClient = new HttpClient($sourceUrl);

        [$scheme, $url] = explode('://', $sourceUrl, 2);
        [$host, $path] = explode('/', $url, 2);

        parent::__construct($filters, $scheme . '://' . $host, $path, $vars);
    }

    public function load(?FilterInterface $additionalFilter = null)
    {
        $this->httpClient->reset();
        $this->httpClient->setMethod('GET');
        try {
            $response = $this->httpClient->send();
            if ($response->isSuccess()) {
                $content = $response->getBody();
            } else {
                $content = false;
            }

        } catch (\Exception $e) {
            if (!$this->ignoreErrors) {
                throw $e;
            }
        }

        if (false === $content && !$this->ignoreErrors) {
            throw new \RuntimeException(sprintf('Unable to load asset from URL "%s"', $this->httpClient->getUri()->toString()));
        }

        $this->doLoad($content, $additionalFilter);
    }

    public function getLastModified()
    {
        $this->httpClient->reset();
        $this->httpClient->setMethod('HEAD');
        try {
            $headers = $this->httpClient->send()->getHeaders();
            if (($header = $headers->get('Last-Modified'))) {
                return strtotime(trim($header->getFieldValue()));
            }
        } catch (\Exception $e) {
            if (!$this->ignoreErrors) {
                throw $e;
            }
        }
    }

}
