<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 12.05.2014
 * Time: 16:58
 */

namespace App\View\Helper;

use ExprAs\Dom\Stdlib\Document;
use ExprAs\Dom\Stdlib\Node as DomNode;
use Laminas\View\Helper\AbstractHelper;

class HilightSyllables extends AbstractHelper
{
    protected $_content;

    public function __invoke($content, $syllables, $tag = 'strong', $attributes = array())
    {
        $content = str_replace(' ', ' ', $content);

        if (!is_array($syllables)){
            $syllables = array($syllables);
        }
        $attributesString = '';
        foreach($attributes as $_k=>$_v){
            $attributesString = sprintf(' %s="%s"', $_k, $_v);
        }


        $element = Document::createElementFromHTML('<root>' . $content . '</root>');
        foreach($syllables as $_s){
            $query = sprintf('//text()[contains(.,"%s")]', $_s);
            $nodes = $element->queryXpath($query);
            /** @var DomNode $_node */
            foreach($nodes as $_node){
                $_node->parentNode->replaceChildWithHtml(str_ireplace($_s, sprintf('<%s%s>%s</%s>', $tag, $attributesString, $_s, $tag), $_node->toString()),
                    $_node);
            }
        }
        $this->_content = $element->innerHTML();


        return $this;
    }

    public function __toString()
    {
        return $this->_content;
    }
} 