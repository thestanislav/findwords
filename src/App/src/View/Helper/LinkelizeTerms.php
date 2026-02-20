<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 12.05.2014
 * Time: 16:58
 */

namespace App\View\Helper;

use App\Entity\Word;
use Doctrine\ORM\EntityManager;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use ExprAs\Doctrine\Repository\DefaultRepository;
use ExprAs\Dom\Stdlib\Document;
use Laminas\View\Helper\AbstractHelper;

class LinkelizeTerms extends AbstractHelper
{
    /** @var  string */
    protected $_content;

    protected $_wordRepository;

    /**
     * @return DefaultRepository
     */
    public function getWordRepository()
    {
        if (!$this->_wordRepository) {
            $this->_wordRepository = $this->getView()->service(EntityManager::class)->getRepository('App\Entity\Word');
        }
        return $this->_wordRepository;
    }


    public function __invoke($content, $refTags = array('kref'))
    {
        $content = str_replace(' ', ' ', $content);
        $content = preg_replace_callback('~\[(https?://[^\]]+)\]~', function($matches){
            return sprintf('<a href="%s" target="_blank"><i class="fas fa-xs fa-external-link-alt"></i></a>', $matches[1]);
        }, $content);

        $element = Document::createElementFromHTML('<root>' . $content . '</root>');
        $query = implode(
            '|',
            array_map(
                function ($tag) {
                    return sprintf('//%s', $tag);
                },
                $refTags
            )
        );

        $nodes = $element->queryXpath($query);
        /** @var DomNode $_node */
        foreach ($nodes as $_node) {

            $searchWord = trim($_node->textContent);
            if ($_node instanceof  \DOMElement  && $_node->hasAttribute('ref')){
                $searchWord = $_node->getAttribute('ref');
                $translit = iconv("UTF-8", "ASCII//TRANSLIT", $searchWord);
                if ($translit != $searchWord && strpos($translit, '?') == false) {
                    $searchWord = $translit;
                }
            }

            if ($wordEntity = $this->getWordRepository()->findOneBy(array('word' => $searchWord))) {
                $html =  sprintf(
                    ' <a href="%s">%s</a>',
                    $this->getView()->url(
                        'dictionary-term',
                        array(
                            'term' => rawurlencode($wordEntity->getWord())
                        )
                    ),
                    $_node->textContent
                );
            } else{
                $html = ' ' . $_node->textContent;
            }

            $_node->parentNode->replaceChildWithHtml($html, $_node);


        }
        $this->_content = $element->innerHTML();


        return $this;
    }

    public function __toString()
    {
        return $this->_content;
    }
} 