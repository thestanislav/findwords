<?php

namespace ExprAs\Dom\Stdlib;

class Element extends \DOMElement implements \Stringable
{
    use NodeUtilsTrait;

    /**
     *
     * @param string $cssQuery
     *
     * @return NodeList
     */
    public function queryCss($cssQuery)
    {
        return $this->ownerDocument->queryCss($cssQuery, $this);
    }


    /**
     * @return null | self
     */
    public function firstChildElement()
    {
        $_node = null;
        foreach ($this->childNodes as $_node) {
            if ($_node instanceof Element) {
                break;
            }
        }

        if (!$_node instanceof Element) {
            return null;
        }
        return $_node;
    }

    /**
     * @return Element|\DOMElement|null
     */
    public function nextSiblingElement()
    {
        $node = $this;
        do {
            $node = $node->nextSibling;
        } while ($node != null && !$node instanceof Element);

        if ($node && $this->isSameNode($node)) {
            return null;
        }
        return $node;
    }

    /**
     * @return Element|\DOMElement|null
     */
    public function previousSiblingElement()
    {
        $node = $this;
        do {
            $node = $node->previousSibling;
        } while ($node != null && !$node instanceof Element);

        if ($node && $this->isSameNode($node)) {
            return null;
        }
        return $node;
    }

    /**
     *
     * @param string $xpathQuery
     *
     * @return \DOMNodeList
     */
    public function queryXpath($xpathQuery)
    {
        return $this->ownerDocument->queryXpath($xpathQuery, $this);
    }

    public function innerHTML($value = null)
    {
        if ($value !== null) {
            while (false != ($_child = $this->firstChild)) {
                $this->removeChild($_child);
            }
            if ($value instanceof \DOMNode || $value instanceof \DOMElement) {
                $this->appendChild($value);
            } elseif ($value instanceof \DOMNodeList) {
                foreach ($value as $_node) {
                    $this->appendChild($_node);
                }
            } elseif (!empty($value)) {
                $doc = clone $this->ownerDocument;
                $body = $doc->getBody();
                $elem = $doc->createElement('div', $value);
                $body->innerHTML($elem->childNodes);
            }

        } else {
            $content = '';
            $doc = clone $this->ownerDocument;
            foreach ($this->childNodes as $child) {
                $content .= $doc->saveHTML($doc->importNode($child, true));
            }
            return $content;
        }
    }

    /**
     * @param  $class
     * @return bool
     */
    public function hasClass($class)
    {
        if (!$this->hasAttribute('class')) {
            return false;
        }
        $cls = preg_split('~\s+~', trim(strtolower($this->getAttribute('class'))));

        return in_array(strtolower((string) $class), $cls);
    }

    public function saveHTML()
    {
        return $this->outerHTML();
    }

    public function outerHTML()
    {
        $doc = $this->ownerDocument;
        $content = $doc->saveHTML($this);
        return $content;
    }

    public function __toString(): string
    {
        return (string) $this->outerHTML();
    }
}
