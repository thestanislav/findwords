<?php

namespace ExprAs\Rest\Hydrator\Configurator;

use ExprAs\Rest\Hydrator\RestHydrator;
use Psr\Container\ContainerInterface;

interface RestHydratorConfiguratorInterface
{
    public function canConfigureStrategies(RestHydrator $hydrator, object $object): bool;

    public function configureStrategies(RestHydrator $hydrator, object $object): void;

    public function canConfigureFilters(RestHydrator $hydrator, object $object): bool;

    public function configureFilters(RestHydrator $_hydrator, object $object): void;
}
