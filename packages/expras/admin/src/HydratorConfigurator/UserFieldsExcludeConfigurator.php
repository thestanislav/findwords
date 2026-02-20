<?php

namespace ExprAs\Admin\HydratorConfigurator;

use ExprAs\Rest\Hydrator\Configurator\AbstractRestHydratorConfigurator;
use ExprAs\Rest\Hydrator\RestHydrator;
use ExprAs\User\Entity\UserSuper;
use Laminas\Hydrator\Filter\FilterProviderInterface;

class UserFieldsExcludeConfigurator extends AbstractRestHydratorConfigurator
{
    #[\Override]
    public function canConfigureFilters(RestHydrator $hydrator, object $object): bool
    {
        return $object instanceof UserSuper;
    }

    public function configureFilters(RestHydrator $_hydrator, object $object): void
    {
        $excludeFields = ['password', 'rememberTokens'];
        if ($object instanceof FilterProviderInterface) {
            $object->getFilter()->addFilter('exclude', fn ($v) => !in_array($v, $excludeFields));
        } else {
            $_hydrator->addFilter('exclude', fn ($v) => !in_array($v, $excludeFields));
        }


    }
}
