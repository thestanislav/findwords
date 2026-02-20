<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/9/2017
 * Time: 23:13
 */

namespace ExprAs\Rest;

use ExprAs\Core\ConfigAggregator\InvokableProvider;
use ExprAs\Core\ModuleConfigProvider\AbstractProvider;

class ConfigProvider extends AbstractProvider
{
    #[\Override]
    public function getDependantModules()
    {
        return [
            new InvokableProvider(\ExprAs\Doctrine\ConfigProvider::class)
        ];
    }

    #[\Override]
    public function getDependencies()
    {
        return [
            'factories' => [
                Helper\ExcelExportHelper::class => Helper\ExcelExportHelperFactory::class,
            ],
        ];
    }
}
