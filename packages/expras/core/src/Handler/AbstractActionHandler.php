<?php

namespace ExprAs\Core\Handler;

use Laminas\Filter\Word\DashToCamelCase;
use Laminas\Stdlib\Parameters;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Abstract handler class that provides action-based request handling and parameter management.
 * 
 * This class implements PSR-15 MiddlewareInterface and provides a framework for handling
 * HTTP requests based on action parameters. It automatically parses and provides access
 * to various request parameters (query, body, cookie, server, files) through getter methods.
 *
 * @package ExprAs\Core\Handler
 */
abstract class AbstractActionHandler implements MiddlewareInterface
{
    use RequestParamsTrait;
    protected $actionKey = 'action';

    private ?\Laminas\Stdlib\Parameters $bodyParams = null;

    private ?\Laminas\Stdlib\Parameters $queryParams = null;

    private ?\Laminas\Stdlib\Parameters $cookieParams = null;

    private ?\Laminas\Stdlib\Parameters $serverParams = null;

    private ?\Laminas\Stdlib\Parameters $uploadedFiles = null;

    /**
     * @return string
     */
    public function getActionKey(): string
    {
        return $this->actionKey;
    }

    /**
     * @return Parameters
     */
    public function getBodyParams(): Parameters
    {

        return $this->bodyParams;
    }

    /**
     * @return Parameters
     */
    public function getQueryParams(): Parameters
    {
        return $this->queryParams;
    }

    /**
     * @return Parameters
     */
    public function getCookieParams(): Parameters
    {
        return $this->cookieParams;
    }

    /**
     * @return Parameters
     */
    public function getServerParams(): Parameters
    {
        return $this->serverParams;
    }

    /**
     * @return Parameters
     */
    public function getUploadedFiles(): Parameters
    {
        return $this->uploadedFiles;
    }




    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!($action = $request->getAttribute($this->actionKey))) {
            return $handler->handle($request);
        }
        if (!method_exists($this, $actionMethod = lcfirst((string) (new DashToCamelCase())->filter($action)) . ucfirst((string) $this->actionKey))) {
            return $handler->handle($request);
        }

        $this->queryParams = new Parameters($request->getQueryParams());
        $this->bodyParams = new Parameters($request->getParsedBody());
        $this->cookieParams = new Parameters($request->getCookieParams());
        $this->serverParams = new Parameters($request->getServerParams());
        $this->uploadedFiles = new Parameters($request->getUploadedFiles());


        return call_user_func_array([$this, $actionMethod], [$request, $handler]);

    }


}
