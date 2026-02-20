<?php
/**
 * Zucchi (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ZucchiAdmin for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */

namespace ExprAs\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * Truncate input text
 *
 * @author     Matt Cockayne <matt@zucchi.co.uk>
 * @package    Zucchi
 * @subpackage View
 * @category   Helper
 */
class Truncate extends AbstractHelper implements \Stringable
{
    protected $_text = '';

    protected $_options = [];

    protected function _setDefaultOptions()
    {
        $this->_options = [
            'length'          => 80,
            //minimum truncate length
            'etc'            => ' ...',
            'breakAfter'     => [],
            'stripTags'      => false,
            'escape'         => true,
        ];
    }

    /**
     * Truncate input text
     *
     * @param  string $text
     * @param  int    $length
     * @param  bool   $wordsafe
     * @param  bool   $escape
     * @return string
     */
    public function __invoke($text, $length = 80, $etc = '...')
    {
        $this->_text = $text;
        $this->_setDefaultOptions();
        $this->_options['length'] = $length;
        $this->_options['etc'] = $etc;
        return $this;
    }

    public function breakSentence($etc = "")
    {
        $this->_options['breakAfter'][] = '.!?;:';
        $this->_options['etc'] = $etc;
        return $this;
    }

    public function breakWord()
    {
        $this->_options['breakAfter'][] = ' ';
        return $this;
    }

    public function stripTags()
    {
        $this->_options['stripTags'] = true;
        return $this;
    }

    public function toString()
    {
        if ($this->_options['length'] == 0) {
            return '';
        }

        if ($this->_options['stripTags']) {
            $this->_text = str_replace('<', ' <', (string) $this->_text);
            $this->_text = str_replace('>', '> ', $this->_text);
            $this->_text = strip_tags($this->_text);
            $this->_text = str_replace('&nbsp;', ' ', $this->_text);
            $this->_text = html_entity_decode($this->_text, ENT_COMPAT);
            $this->_text = preg_replace('~\s+~u', ' ', $this->_text);
            $this->_text = trim((string) preg_replace('~[\[\]]{2,}~u', ' ', (string) $this->_text));
        }

        if (mb_strlen((string) $this->_text) > $this->_options['length']) {

            $length = $this->_options['length'];
            $length -= min($length, mb_strlen((string) $this->_options['etc']));

            if ((is_countable($breakAfter = $this->_options['breakAfter']) ? count($breakAfter = $this->_options['breakAfter']) : 0) > 0) {

                $breakAfter = array_map('preg_quote', $breakAfter);
                $pattern = '~^.{' . $length . '}[^' . implode('', $breakAfter) . ']*[' . implode('', $breakAfter) . ']*~smu';

                $m = [];
                preg_match($pattern, (string) $this->_text, $m);
                $this->_text =  $m[0] . $this->_options['etc'];

            } else {
                $this->_text = mb_substr((string) $this->_text, 0, $length) . $this->_options['etc'];
            }
        }
        return $this->_text;
    }


    public function __toString(): string
    {
        return (string) $this->toString();
    }

    public function __call($method, $args)
    {
        if (array_key_exists($method, $this->_options)) {
            if ($method == 'breakAfter') {
                foreach ($args as $_arg) {
                    $this->_options['breakAfter'] = array_merge($this->_options['breakAfter'], str_split((string) $_arg, 1));
                }
                $this->_options['breakAfter'] = array_unique($this->_options['breakAfter']);
            } else {
                $this->_options['breakAfter'] = array_shift($args);
            }
            return $this;
        }

        throw new \RuntimeException('Called to unknown method ' . $method);
    }
}
