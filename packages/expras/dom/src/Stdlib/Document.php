<?php

namespace ExprAs\Dom\Stdlib;

use TheSeer\CSS2XPath\Translator;
use Laminas\Http\Client;

class Document extends \DOMDocument implements \Stringable
{
    final public const int utf8_split_length = 5000;

    public $cleanRepair = false;

    public $url = null;

    public function __construct(string $version = "1.0", string $encoding = "utf-8")
    {
        parent::__construct($version, 'utf-8');

        $this->registerNodeClass('DOMDocument', \ExprAs\Dom\Stdlib\Document::class);
        $this->registerNodeClass('DOMElement', \ExprAs\Dom\Stdlib\Element::class);
        $this->registerNodeClass('DOMNode', \ExprAs\Dom\Stdlib\Node::class);
        $this->registerNodeClass('DOMText', \ExprAs\Dom\Stdlib\Text::class);
        $this->registerNodeClass('DOMAttr', \ExprAs\Dom\Stdlib\Attr::class);
        $this->preserveWhiteSpace = false;
        $this->formatOutput = true;
        $this->recover = true;
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
     * @param string $cssQuery
     *
     * @return \ExprAs\Dom\Stdlib\NodeList
     */
    public function queryCss($cssQuery, ?Element $element = null)
    {
        $xpath = (new Translator())->translate($cssQuery);
        if ($element && (str_starts_with($xpath, '//') || str_contains($xpath, '|//'))) {

            //check multiple selects
            $tmp = explode('|', $xpath);
            if (count($tmp) == 1) {
                $xpath = substr($xpath, 2);
            } else {
                $tmp = array_map(
                    fn ($el) => ltrim((string) $el, '/'),
                    $tmp
                );
                $xpath = implode('|', $tmp);
            }
        }
        return $this->queryXpath($xpath, $element);
    }

    /**
     *
     * @param string $xpathQuery
     * @param \DOMNode   $node
     *
     * @return NodeList
     */
    public function queryXpath($xpathQuery, ?\DOMNode $node = null)
    {
        $doc = $this;
        /*if ($node){
            $doc = clone $this;
            $doc->loadHTML('<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><body></body></html>');
            $doc->getBody()->innerHTML($doc->importNode($node, true));
        }*/
        /*if ($node){
            $xpathQuery = '*' . $xpathQuery;
        }*/
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace("php", "http://php.net/xpath");
        $xpath->registerPhpFunctions();
        return new NodeList($xpath->evaluate($xpathQuery, $node, false) ?: null);
    }

    /**
     *
     * @param string $source
     *
     * @return Element
     */
    public static function createElementFromHTML($source)
    {
        if (!self::isUTF8($source)) {
            //$source = iconv($dom->encoding, 'utf-8', $source);
        }
        $source
            = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>' . $source
            . ' ' . '</body></html>';
        $dom = new self();
        $dom->loadHTML($source);

        $_child = null;
        foreach ($dom->getBody()->childNodes as $_child) {
            if ($_child instanceof Element) {
                break;
            }
        }
        return $_child;
    }

    public function loadHTML(string $source, int $options = 0): bool
    {
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML($source);
        $isUtfSource = self::isUTF8($source);
        if (strcasecmp((string)$dom->encoding, 'utf-8') !== 0) {
            if ($dom->encoding) {
                if (!$isUtfSource) {
                    $source = str_ireplace($dom->encoding, 'utf-8', $source);
                    $source = iconv($dom->encoding, 'utf-8', $source);
                } else {
                    $source = preg_replace('~charset=[^"\'\>]+~', 'charset=utf-8', $source);
                }
            } else {
                $source = preg_replace(
                    '~<head[^\>]*>~i',
                    '\0<meta http-equiv="Content-Type" content="text/html; charset=utf-8">',
                    $source
                );
                if (!$isUtfSource) {
                    $source = iconv(iconv_get_encoding('input_encoding'), 'utf-8', (string) $source);
                }
            }
        }
        //replace all nbsp, there are making big problems :)
        $source = str_replace('&nbsp;', ' ', $source);

        $source = iconv('utf-8', 'utf-8//IGNORE', $source);

        $source = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . $source;

        $result = parent::loadHTML($source, $options);
        $this->encoding = 'UTF-8';
        //die($this);

        libxml_use_internal_errors(false);
        return $result;
    }

    public function loadHTMLUri($uri, $config = [])
    {
        if (!$uri instanceof Client) {
            $uri = preg_replace_callback(
                '~\s~',
                fn ($m) => ord($m[0]),
                (string) $uri
            );
            $uri = new Client($uri, $config);
        }
        $this->url = $uri->getUri();
        $this->request = $uri;
        /*
        $curl = new Client\Adapter\Curl();
        $curl->setCurlOption(CURLOPT_TIMEOUT, 5);
        $uri->setAdapter($curl);
        */

        $uri->getRequest()->getHeaders()->addHeaderLine(
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:10.0) Gecko/2010' . random_int(0, 1000) . ' Firefox/70.0'
        )
            ->addHeaderLine('Accept-Language: ru-ru,ru');
        $content = $uri->send()->getBody();
        //$content = file_get_contents($uri->getUri()->toString());
        //$content = shell_exec('wget -qO- ' . $uri->getUri()->toString());

        return $this->loadHTML($content);
    }

    public function loadHTMLFile(string $filename, int $options = 0): bool
    {
        if (!is_file($filename)) {
            throw new \DOMException('Could not find filename', null, null);
        }
        return $this->loadHTML(file_get_contents($filename));
    }

    /**
     * @return Element
     */
    public function getBody()
    {
        return $this->getElementsByTagName('body')->item(0);
    }
    /*
        public function saveHTML(?\DOMNode $node = null): bool|string
        {
            if (version_compare(PHP_VERSION, '5.3.6') >= 0) {
                return parent::saveHTML($node);
            } else {
                if ($node) {
                    $doc = clone $this;
                    $doc->loadHTML(
                        '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><body></body></html>'
                    );
                    $doc->getBody()->innerHTML($doc->importNode($node, true));
                    $html = $doc->saveHTML();
                    $html = substr($html, stripos($html, '<body>') + 6);
                    $html = substr($html, 0, strrpos($html, '</body>'));
                    return $html;
                } else {
                    return parent::saveHTML();
                }
            }
        }
    */
    public function __toString(): string
    {
        return (string) $this->saveHTML();
    }

    public static function isUTF8($string)
    {

        if (function_exists('mb_check_encoding')) {
            return mb_check_encoding($string, 'utf-8');
        }

        if (strlen((string) $string) > self::utf8_split_length) {
            for (
                $i = 0, $s = self::utf8_split_length, $j = ceil(strlen((string) $string) / self::utf8_split_length); $i < $j;
                $i++, $s += self::utf8_split_length
            ) {
                if (self::isUTF8(substr((string) $string, $s, self::utf8_split_length))) {
                    return true;
                }
            }
            return false;
        }
        // From http://w3.org/International/questions/qa-forms-utf-8.html
        return preg_match(
            '%^(?:
                                        [\x09\x0A\x0D\x20-\x7E]             # ASCII
                                        | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
                                        |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
                                        | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
                                        |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
                                        |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
                                        | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
                                        |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
                                )*$%xs',
            (string) $string
        );
    }
}
