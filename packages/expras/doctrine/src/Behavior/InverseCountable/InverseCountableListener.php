<?php
/**
 * Created by JetBrains PhpStorm.
 * User: stas
 * Date: 27.12.12
 * Time: 0:34
 * To change this template use File | Settings | File Templates.
 */

namespace ExprAs\Doctrine\Behavior\InverseCountable;

use Gedmo\Mapping\MappedEventSubscriber;
use Doctrine\Common\EventArgs;

class InverseCountableListener extends MappedEventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            'onFlush',
            'loadClassMetadata',
            'prePersist',
        ];
    }

    protected function getNamespace()
    {
        return __NAMESPACE__;
    }
}
