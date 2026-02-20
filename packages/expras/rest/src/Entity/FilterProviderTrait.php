<?php

namespace ExprAs\Rest\Entity;

use Laminas\Hydrator\Filter\FilterComposite;
use Laminas\Hydrator\Filter\FilterInterface;

trait FilterProviderTrait
{
    /**
     * @var ?FilterInterface
     */
    protected ?FilterInterface $__filter = null;

    /**
     * @return FilterComposite
     */
    public function getFilter(): FilterInterface
    {
        if (!$this->__filter) {
            $this->__filter = new FilterComposite();
        }
        return $this->__filter;
    }
}
