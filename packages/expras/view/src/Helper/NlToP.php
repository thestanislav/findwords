<?php

namespace ExprAs\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class NlToP extends AbstractHelper
{
    /**
     * @param  $text
     * @param  array $attributes
     * @return string
     */
    public function __invoke($text, $attributes = [], $minNl = 2)
    {
        if (!mb_strlen(trim((string) $text))) {
            return '';
        }
        $paragraphs = preg_split('~[\r\n]{' . max(1, $minNl) . ',}~', (string) $text);
        foreach ($paragraphs as $_k => $_v) {
            $paragraphs[$_k] = '<p ';
            foreach ($attributes as $_name => $_value) {
                $paragraphs[$_k] .= sprintf('%s="%s"', $_name, $_value);
            }
            $paragraphs[$_k] .= '>' . nl2br($_v) . '</p>';
        }
        return implode('', $paragraphs);
    }
}
