<?php
/**
 * Created by JetBrains PhpStorm.
 * User: stas
 * Date: 27.11.12
 * Time: 13:18
 * To change this template use File | Settings | File Templates.
 */

namespace ExprAs\Core\Cache;

use ExprAs\Core\Module\Bootstrapper\AbstractBootstrapper;
use Laminas\Mvc\MvcEvent;

class Bootstrapper extends AbstractBootstrapper
{
    public function bootstrap(MvcEvent $event)
    {
        $app = $event->getApplication();
        $sm = $app->getServiceManager();
        $cm = $sm->get('as.cachemanager.default');

        /**
        $controllerLoader = $sm->get('ControllerLoader');
        $controllerLoader->addInitializer(
            function ($instance) use ($cm) {
                $reflection = new \ReflectionObject($instance);
                $traitNames = array();

                do {
                    $traitNames = array_merge($traitNames, $reflection->getTraitNames());
                }while($reflection = $reflection->getParentClass());

                if (in_array('ExprAs\Core\Cache\CacheManagerAwareTrait', $traitNames)){
                    $instance->setCacheManager($cm);
                }
            }
        );
         **/

        $sm->addInitializer(
            function ($instance) use ($cm) {

                if (!is_object($instance)) {
                    return;
                }

                $reflection = new \ReflectionObject($instance);
                $traitNames = [];

                do {
                    $traitNames = [...$traitNames, ...$reflection->getTraitNames()];
                } while ($reflection = $reflection->getParentClass());

                if (in_array(\ExprAs\Core\Cache\CacheManagerAwareTrait::class, $traitNames)) {
                    $instance->setCacheManager($cm);
                }
            }
        );

    }
}
