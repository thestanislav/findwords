<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ExprAs\Core\Handler;

use Laminas\Http\Header\HeaderInterface;
use Laminas\Stdlib\Parameters;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

class AbstractActionParams
{
    
    private readonly Parameters $bodyParams;

    private readonly Parameters $queryParams;

    private readonly Parameters $cookieParams;

    private readonly Parameters $serverParams;

    private readonly Parameters $uploadedFiles;
    private readonly Parameters $headerParams;

    private readonly Parameters $routeParams;

    /**
     * Grabs a param from route match by default.
     *
     * @param string $param
     * @param mixed  $default
     *
     * @return mixed
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->queryParams = new Parameters($request->getQueryParams());
        $this->bodyParams = new Parameters($request->getParsedBody());
        $this->cookieParams = new Parameters($request->getCookieParams());
        $this->serverParams = new Parameters($request->getServerParams());
        $this->uploadedFiles = new Parameters($request->getUploadedFiles());
        $this->headerParams = new Parameters($request->getHeaders());
        $this->routeParams = new Parameters($request->getAttributes());
    }

    /**
     * Return all uploaded files or a single uploaded file.
     *
     * @param $name
     * @param $default
     *
     * @return UploadedFileInterface | UploadedFileInterface[] | null.
     */
    public function fromUploadedFiles($name = null, $default = null)
    {
        if ($name === null) {
            return $this->uploadedFiles->getArrayCopy();
        }

        return $this->uploadedFiles->get($name, $default);
    }

    /**
     * Return all header parameters or a single header parameter.
     *
     * @param string $header  Header name to retrieve, or null to get all.
     * @param mixed  $default Default value to use when the requested header is missing.
     *
     * @return null|HeaderInterface | HeaderInterface[] | HeaderInterface[][]
     */
    public function fromHeader($header = null, $default = null)
    {
        if ($header === null) {
            return $this->headerParams->getArrayCopy();
        }

        return $this->headerParams->get($header, $default);
    }

    /**
     * Return all post parameters or a single post parameter.
     *
     * @param string $param   Parameter name to retrieve, or null to get all.
     * @param mixed  $default Default value to use when the parameter is missing.
     *
     * @return mixed
     */
    public function fromParsedBody($param = null, $default = null)
    {
        if ($param === null) {
            return $this->bodyParams->toArray();
        }

        return $this->bodyParams->get($param, $default);
    }

    /**
     * Return all query parameters or a single query parameter.
     *
     * @param string $param   Parameter name to retrieve, or null to get all.
     * @param mixed  $default Default value to use when the parameter is missing.
     *
     * @return mixed
     */
    public function fromQuery($param = null, $default = null)
    {
        if ($param === null) {
            return $this->queryParams->toArray();
        }

        return $this->queryParams->get($param, $default);
    }

    /**
     * Return all cookie parameters or a single cookie parameter.
     *
     * @param string $param   Parameter name to retrieve, or null to get all.
     * @param mixed  $default Default value to use when the parameter is missing.
     *
     * @return mixed
     */
    public function fromCookie($param = null, $default = null)
    {
        if ($param === null) {
            return $this->cookieParams->toArray();
        }

        return $this->cookieParams->get($param, $default);
    }

    /**
     * Return all server parameters or a single server parameter.
     *
     * @param string $param   Parameter name to retrieve, or null to get all.
     * @param mixed  $default Default value to use when the parameter is missing.
     *
     * @return mixed
     */
    public function fromServer($param = null, $default = null)
    {
        if ($param === null) {
            return $this->serverParams->toArray();
        }

        return $this->serverParams->get($param, $default);
    }

    /**
     * Return all server parameters or a single server parameter.
     *
     * @param string $param   Parameter name to retrieve, or null to get all.
     * @param mixed  $default Default value to use when the parameter is missing.
     *
     * @return mixed
     */
    public function fromRoute($param = null, $default = null)
    {
        if ($param === null) {
            return $this->routeParams->toArray();
        }

        return $this->routeParams->get($param, $default);
    }


}
