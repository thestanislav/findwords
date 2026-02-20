<?php

namespace ExprAs\Helpers\Pluralize;

use Laminas\I18n\Translator\Plural\Rule as PluralRule;

class PluralizeHelper
{
    protected PluralRule $_pluralRule;

    public function __construct(string $pluralRule)
    {
        $this->_pluralRule = PluralRule::fromString($pluralRule);
    }

    public function __invoke(array $strings, int $number): string
    {

        if (!is_array($strings)) {
            $strings = (array)$strings;
        }

        $pluralIndex = $this->_pluralRule->evaluate($number);

        return $strings[$pluralIndex];
    }
}
