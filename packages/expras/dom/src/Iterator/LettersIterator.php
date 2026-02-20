<?php
/**
 * Author: Stanislav Anisimov<stanislav@ww9.ru>
 * Date: 10.03.13
 * Time: 12:15
 */

namespace ExprAs\Dom\Iterator;

use Iterator;
use DOMNode;
use DOMDocument;
use DOMElement;

/**
 * Iterates individual characters (Unicode codepoints) of DOM text and CDATA nodes
 * while keeping track of their position in the document.
 *
 * Example:
 *
 *  $doc = new DOMDocument();
 *  $doc->load('example.xml');
 *  foreach(new DOMLettersIterator($doc) as $letter) echo $letter;
 *
 * NB: If you only need characters without their position
 *     in the document, use DOMNode->textContent instead.
 *
 * @author  porneL http://pornel.net
 * @license Public Domain
 */
class LettersIterator implements Iterator
{
    private $start;
    private $current;
    private $offset;
    private $key;
    private $letters;

    /**
     * expects DOMElement or DOMDocument (see DOMDocument::load and DOMDocument::loadHTML)
     */
    public function __construct(DOMNode $el)
    {
        if ($el instanceof DOMDocument) {
            $this->start = $el->documentElement;
        } else {
            if ($el instanceof DOMElement) {
                $this->start = $el;
            } else {
                throw new Exception\InvalidArgumentException("Invalid arguments, expected DOMElement or DOMDocument");
            }
        }
    }

    /**
     * Returns position in text as DOMText node and character offset.
     * (it's NOT a byte offset, you must use mb_substr() or similar to use this offset properly).
     * node may be NULL if iterator has finished.
     *
     * @return array
     */
    public function currentTextPosition()
    {
        return [$this->current, $this->offset];
    }

    /**
     * Returns DOMElement that is currently being iterated or NULL if iterator has finished.
     *
     * @return DOMElement
     */
    public function currentElement()
    {
        return $this->current ? $this->current->parentNode : null;
    }

    // Implementation of Iterator interface
    public function key()
    {
        return $this->key;
    }

    public function next()
    {
        if (!$this->current) {
            return;
        }

        if ($this->current->nodeType == XML_TEXT_NODE || $this->current->nodeType == XML_CDATA_SECTION_NODE) {
            if ($this->offset == -1) {
                // fastest way to get individual Unicode chars and does not require mb_* functions
                preg_match_all('/./us', (string) $this->current->textContent, $m);
                $this->letters = $m[0];
            }
            $this->offset++;
            $this->key++;
            if ($this->offset < (is_countable($this->letters) ? count($this->letters) : 0)) {
                return;
            }
            $this->offset = -1;
        }

        while ($this->current->nodeType == XML_ELEMENT_NODE && $this->current->firstChild) {
            $this->current = $this->current->firstChild;
            if ($this->current->nodeType == XML_TEXT_NODE || $this->current->nodeType == XML_CDATA_SECTION_NODE) {
                return $this->next();
            }
        }

        while (!$this->current->nextSibling && $this->current->parentNode) {
            $this->current = $this->current->parentNode;
            if ($this->current === $this->start) {
                $this->current = null;
                return;
            }
        }

        $this->current = $this->current->nextSibling;

        return $this->next();
    }

    public function current()
    {
        if ($this->current) {
            return $this->letters[$this->offset];
        }
        return null;
    }

    public function valid()
    {
        return !!$this->current;
    }

    public function rewind()
    {
        $this->offset = -1;
        $this->letters = [];
        $this->current = $this->start;
        $this->next();
    }
}
