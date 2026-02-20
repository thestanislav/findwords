<?php

namespace ExprAs\Rest\Hydrator\Configurator;

use ExprAs\Rest\Hydrator\RestHydrator;

abstract class AbstractRestHydratorConfigurator implements RestHydratorConfiguratorInterface
{
    public function canConfigureStrategies(RestHydrator $hydrator, object $object): bool
    {
        return false;
    }

    public function configureStrategies(RestHydrator $hydrator, object $object): void
    {

    }


    public function canConfigureFilters(RestHydrator $hydrator, object $object): bool
    {
        return false;
    }

    public function configureFilters(RestHydrator $_hydrator, object $object): void
    {

    }
}
