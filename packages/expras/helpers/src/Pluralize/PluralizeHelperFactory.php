<?php

namespace ExprAs\Helpers\Pluralize;

use Gettext\Languages\Language;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class PluralizeHelperFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): PluralizeHelper
    {
        $pluralConfig = $container->get('config')['expras-pluralize-helper'];
        $lang = $options['language'] ?? $pluralConfig['defaultLanguage'];
        if (class_exists(Language::class)) {
            $info = Language::getById($lang);
            $formula = sprintf('nplurals=%d; plural=%s', is_countable($info->categories) ? count($info->categories) : 0, $info->formula);
        } else {
            $formula = $pluralConfig['defaultFormula'];
        }

        return new PluralizeHelper($formula);
    }
}
