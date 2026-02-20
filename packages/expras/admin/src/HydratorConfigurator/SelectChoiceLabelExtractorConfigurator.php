<?php

namespace ExprAs\Admin\HydratorConfigurator;

use ExprAs\Admin\Hydrator\Strategy\SelectChoiceLabelExtractor;
use ExprAs\Rest\Hydrator\Configurator\AbstractRestHydratorConfigurator;
use ExprAs\Rest\Hydrator\RestHydrator;

class SelectChoiceLabelExtractorConfigurator extends AbstractRestHydratorConfigurator
{
    /**
     * SelectChoiceLabelExtractorConfigurator constructor.
     *
     * @param $_fields
     */
    public function __construct(protected $_fields)
    {
    }


    #[\Override]
    public function canConfigureStrategies(RestHydrator $hydrator, object $object): bool
    {
        return array_key_exists($object::class, $this->_fields);
    }

    public function configureStrategies(RestHydrator $hydrator, object $object): void
    {
        foreach ($this->_fields[$object::class] as $_field => $_choices) {
            $hydrator->addStrategy($_field, new SelectChoiceLabelExtractor($_choices));
        }
    }
}
