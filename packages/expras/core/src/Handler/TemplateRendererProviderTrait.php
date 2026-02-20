<?php

namespace ExprAs\Core\Handler;

use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use Mezzio\Template\TemplateRendererInterface;

trait TemplateRendererProviderTrait
{
    use ServiceContainerAwareTrait;

    /**
     * @var TemplateRendererInterface
     */
    protected $renderer;

    /**
     * @return TemplateRendererInterface
     */
    public function getRenderer(): TemplateRendererInterface
    {
        if (!$this->renderer) {
            $this->renderer = $this->getContainer()->get(TemplateRendererInterface::class);
        }
        return $this->renderer;
    }

    public function render($name, $data = []): string
    {
        return $this->getRenderer()->render($name, $data);
    }

}
