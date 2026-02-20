<?php
/**
 * Created by JetBrains PhpStorm.
 * User: stas
 * Date: 27.11.12
 * Time: 13:18
 * To change this template use File | Settings | File Templates.
 */

namespace ExprAs\Core\PhpSettings;

use ExprAs\Core\Module\Bootstrapper\AbstractBootstrapper;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;

class Bootstrapper extends AbstractBootstrapper
{
    public function init(ModuleManager $manager)
    {
        $manager->getEventManager()->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, $this->setPhpSettings(...));
    }

    public function setPhpSettings(ModuleEvent $event)
    {
        $sl = $event->getParam('ServiceManager');
        $sl->get('phpsettings_manager');
    }
}
