<?php

namespace App\Navigation;


use Mimmi20\Mezzio\Navigation\Service\NavigationFactoryTrait;

/**
 * Default navigation factory.
 *
 * @category  Admin
 */
class DictionaryNavigationFactory
{
    use NavigationFactoryTrait;

    /**
     * creates the DefaultNavigationFactory.
     */
    public function __construct()
    {
        $this->configName = 'dictionary';
    }


}
