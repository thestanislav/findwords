<?php

namespace ExprAs\Dom\Stdlib;

class NodeList implements \Countable, \Stringable, \Iterator
{
    private int $_position = 0;

    public readonly int $length;

    public function __construct(private readonly ?\DOMNodeList $_nodeList = null)
    {
        $this->_position = 0;
        $this->length = $_nodeList->length;
    }

    /**
     * @return integer
     */
    public function count(): int
    {
        return $this->_nodeList->count();
    }

    public function item($index): \DOMElement|\DOMNode|\DOMNameSpaceNode|null
    {
        if ($this->length - 1 < $index) {
            return null;
        }
        return $this->_nodeList->item($index);
    }

    public function rewind(): void
    {
        $this->_position = 0;
    }

    public function current(): \DOMElement|\DOMNode|\DOMNameSpaceNode|null
    {
        return $this->item($this->_position);
    }

    public function key(): int
    {
        return $this->_position;
    }

    public function next(): void
    {
        ++$this->_position;
    }

    public function valid(): bool
    {
        return !is_null($this->current());
    }

    public function saveHTML(): string
    {
        if ($this->length == 0) {
            return '';
        }
        $content = '';
        foreach ($this as $_node) {
            if ($_node instanceof Element) {
                $content .= $_node->saveHTML();
            } else {
                $content .= $_node->textContent;
            }
        }
        return $content;
    }

    public function __toString(): string
    {
        return (string) $this->saveHTML();
    }
}
