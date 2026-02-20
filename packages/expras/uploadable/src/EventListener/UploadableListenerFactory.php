<?php
namespace ExprAs\Uploadable\EventListener;

use ExprAs\Uploadable\EventListener\UploadableListener;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Gedmo\Mapping\Driver\AttributeReader;
class UploadableListenerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): UploadableListener
    {
        $listener = new UploadableListener();
        // This call configures the listener to use the attribute driver service created above; if using annotations, you will need to provide the appropriate service instead
        $listener->setAnnotationReader(new AttributeReader());

        return $listener;
    }
}