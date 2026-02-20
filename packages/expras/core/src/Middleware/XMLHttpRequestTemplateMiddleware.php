<?php

declare(strict_types=1);

namespace ExprAs\Core\Middleware;

use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use Laminas\View\Renderer\RendererInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function in_array;

class XMLHttpRequestTemplateMiddleware implements MiddlewareInterface
{
    use ServiceContainerAwareTrait;

    protected $_renderer;

    public function getTemplateRenderer()
    {
        if (!$this->_renderer) {
            $this->_renderer = $this->getContainer()->get(RendererInterface::class);
        }
        return $this->_renderer ;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->getContainer()->has(RendererInterface::class) && in_array('XMLHttpRequest', $request->getHeader('X-Requested-With'), true)) {
            (function ($template) {
                $template->layout = false;
            })->bindTo($this->getTemplateRenderer(), $this->getTemplateRenderer())($this->getTemplateRenderer());
        }

        return $handler->handle($request);
    }
}
