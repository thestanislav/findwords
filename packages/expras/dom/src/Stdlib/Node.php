<?php

namespace ExprAs\Dom\Stdlib;

class Node extends \DOMNode
{
    use NodeUtilsTrait;

    public function toString()
    {
        return $this->textContent;
    }
}
