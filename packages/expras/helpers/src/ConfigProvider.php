<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/9/2017
 * Time: 00:00
 */

namespace ExprAs\Helpers;

use ExprAs\Core\ModuleConfigProvider\AbstractProvider;
use ExprAs\Helpers\Pluralize\PluralizeHelper;
use ExprAs\Helpers\Pluralize\PluralizeHelperFactory;

class ConfigProvider extends AbstractProvider
{
    /**
     * Returns the container dependencies
     *
     * @return array
     */
    #[\Override]
    public function getDependencies()
    {
        return [
            'invokables' => [

            ],
            'factories' => [
                PluralizeHelper::class => PluralizeHelperFactory::class
            ],
            'aliases' => [

            ]
        ];
    }

}
