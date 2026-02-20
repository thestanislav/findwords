<?php

namespace ExprAs\Dom\Stdlib;

class Text extends \DOMText
{
    use NodeUtilsTrait;

    public function toString()
    {
        return $this->wholeText;
    }

}
