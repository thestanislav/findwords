<?php

namespace ExprAs\Dom\Stdlib;

class Attr extends \DOMAttr implements \Stringable
{
    public function rootNode()
    {
        $node = $this;
        while (!$node->parentNode) {
            $node = $node->parentNode;
        };
        return $node;
    }

    public function remove()
    {
        $this->ownerElement->removeAttribute($this->name);
    }

    public function firstChildElement()
    {
        $self = $this->ownerElement;
        $_node = false;
        foreach ($self->childNodes as $_node) {
            if ($_node instanceof Element) {
                break;
            }
        }

        if (!$_node instanceof Element) {
            return null;
        }
        return $_node;
    }

    public function nextSiblingElement()
    {
        $node = $this->ownerElement;
        do {
            $node = $node->nextSibling;
        } while ($node != null && !$node instanceof Element);

        if ($this->isSameNode($node)) {
            return null;
        }
        return $node;
    }

    /**
     *
     * @param string $xpathQuery
     *
     * @return \DOMNameList
     */
    public function queryXpath($xpathQuery)
    {
        return $this->ownerElement->ownerDocument->queryXpath($xpathQuery, $this);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
