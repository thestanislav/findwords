<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 12.05.2014
 * Time: 17:55
 */

namespace ExprAs\Dom\Stdlib;

trait NodeUtilsTrait
{
    /**
     *
     * @param string $xpathQuery
     * @retun \DOMNameList
     */
    public function queryXpath($xpathQuery)
    {
        return $this->ownerDocument->queryCss($xpathQuery, $this);
    }

    public function rootNode()
    {
        $node = $this;
        while (!$node->parentNode) {
            $node = $node->parentNode;
        };
        return $node;
    }


    /**
     *
     * @return \DOMNode New Node
     */
    public function insertAfter(\DOMNode $newNode, ?\DOMNode $refNode = null)
    {
        if ($refNode) {
            if ($refNode->nextSibling) {
                $refNode->parentNode->insertBefore($newNode, $refNode->nextSibling);
            } else {
                $refNode->parentNode->appendChild($newNode);
            }

        } else {
            $this->parentNode->appendChild($newNode);
        }

        return $newNode;
    }

    /**
     * @param $html
     *
     * @return $this
     */
    public function replaceChildWithHtml($html, ?\DOMNode $oldNode)
    {
        $element = Document::createElementFromHTML(sprintf('<div>%s</div>', $html));
        /**
 * @var Node $firstNode 
*/
        $doc = $this->ownerDocument;
        foreach ($element->childNodes as $_node) {
            $_newNode = $doc->importNode($_node->cloneNode(true), true);
            $this->insertBefore($_newNode, $oldNode);
        }
        $this->removeChild($oldNode);
        return $this;
    }


    public function __toString()
    {
        return $this->toString();
    }


}
