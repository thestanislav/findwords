<?php

namespace ExprAs\View\Helper;

use DOMDocument;
use DOMText;
use DOMNode;
use ExprAs\Dom\Iterator\LettersIterator;
use ExprAs\Dom\Iterator\WordsIterator;
use ExprAs\Dom\Stdlib\Document;
use Laminas\Stdlib\Exception;
use Laminas\View\Helper\AbstractHelper;

/**
 * Truncate input text
 *
 * @author     Matt Cockayne <matt@zucchi.co.uk>
 * @package    Zucchi
 * @subpackage View
 * @category   Helper
 */
class TruncateHtml extends AbstractHelper
{
    final public const string MODE_WORD = 'word';
    final public const string MODE_CHAR = 'char';

    /**
     * Truncate input text
     *
     * @param  string $text
     * @param  int    $length
     * @param  bool   $wordsafe
     * @param  bool   $escape
     * @return string
     */
    public function __invoke($html, $limit, $ellipsis = ' ...', $mode = self::MODE_WORD)
    {
        if ($mode == self::MODE_WORD) {
            return $this->truncateWords($html, $limit, $ellipsis);
        } elseif ($mode == self::MODE_CHAR) {
            return $this->truncateWords($html, $limit, $ellipsis);
        } else {
            throw new Exception\InvalidArgumentException(sprintf('Undefined mode %s', $mode));
        }
    }

    public function truncateChars($html, $limit, $ellipsis = '...')
    {

        if ($limit <= 0 || $limit >= strlen(strip_tags((string) $html))) {
            return $html;
        }

        $dom = new Document('1.0', 'utf-8');
        $dom->loadHTML(sprintf('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta charset="utf-8"/></head><body>%s</body></html>', $html));

        $body = $dom->getElementsByTagName("body")->item(0);

        $it = new LettersIterator($body);

        foreach ($it as $letter) {
            if ($it->key() >= $limit) {
                $currentText = $it->currentTextPosition();
                $currentText[0]->nodeValue = substr((string) $currentText[0]->nodeValue, 0, $currentText[1] + 1);
                $this->removeProceedingNodes($currentText[0], $body);
                $this->insertEllipsis($currentText[0], $ellipsis);
                break;
            }
        }

        return preg_replace('~<(?:!DOCTYPE|/?(?:html|head|meta|body))[^>]*>\s*~i', '', $dom->saveHTML());
    }

    public function truncateWords($html, $limit, $ellipsis = '...')
    {

        if ($limit <= 0 || $limit >= $this->countWords(strip_tags((string) $html))) {
            return $html;
        }

        $dom = new DOMDocument('1.0', 'utf-8');
        @$dom->loadHTML(
            sprintf(
                '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta
        charset="utf-8"/></head><body>%s</body></html>', $html
            )
        );

        $body = $dom->getElementsByTagName("body")->item(0);

        $it = new WordsIterator($body);

        foreach ($it as $word) {
            if ($it->key() >= $limit) {
                $currentWordPosition = $it->currentWordPosition();
                $curNode = $currentWordPosition[0];
                $offset = $currentWordPosition[1];
                $words = $currentWordPosition[2];

                $curNode->nodeValue = substr((string) $curNode->nodeValue, 0, $words[$offset][1] + strlen((string) $words[$offset][0]));

                $this->removeProceedingNodes($curNode, $body);
                $this->insertEllipsis($curNode, $ellipsis);
                break;
            }
        }

        return preg_replace('~<(?:!DOCTYPE|/?(?:html|head|meta|body))[^>]*>\s*~i', '', $dom->saveHTML());
    }

    private function removeProceedingNodes(DOMNode $domNode, DOMNode $topNode)
    {
        if ($topNode->isSameNode($domNode)) {
            return;
        }
        while ($domNode->nextSibling) {
            $next = $domNode->nextSibling;
            $next->parentNode->removeChild($next);
        }
        // when no elements left scan upwards
        $this->removeProceedingNodes($domNode->parentNode, $topNode);



        /*$nextNode = $domNode->nextSibling;
        if ($nextNode !== NULL) {
            $this->removeProceedingNodes($nextNode, $topNode);
            $domNode->parentNode->removeChild($nextNode);
        } else {
            //scan upwards till we find a sibling
            $curNode = $domNode->parentNode;
            while (!$topNode->isSameNode($curNode)) {
                if ($curNode->nextSibling !== NULL) {
                    $curNode = $curNode->nextSibling;
                    $this->removeProceedingNodes($curNode, $topNode);
                    $curNode->parentNode->removeChild($curNode);
                    break;
                }
                $curNode = $curNode->parentNode;
            }
        }*/
    }

    private function insertEllipsis(DOMNode $domNode, $ellipsis)
    {
        $avoid = ['a', 'strong', 'em', 'h1', 'h2', 'h3', 'h4', 'h5']; //html tags to avoid appending the ellipsis to

        if (in_array($domNode->parentNode->nodeName, $avoid) && $domNode->parentNode->parentNode !== null) {
            // Append as text node to parent instead
            $textNode = new DOMText($ellipsis);

            if ($domNode->parentNode->parentNode->nextSibling) {
                $domNode->parentNode->parentNode->insertBefore($textNode, $domNode->parentNode->parentNode->nextSibling);
            } else {
                $domNode->parentNode->parentNode->appendChild($textNode);
            }
        } else {
            // Append to current node
            $domNode->nodeValue = rtrim((string) $domNode->nodeValue) . $ellipsis;
        }
    }

    private function countWords($text)
    {
        $words = preg_split("/[\n\r\t ]+/", (string) $text, -1, PREG_SPLIT_NO_EMPTY);
        return is_countable($words) ? count($words) : 0;
    }
}
