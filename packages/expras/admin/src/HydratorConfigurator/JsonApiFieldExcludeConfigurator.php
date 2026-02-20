<?php

namespace ExprAs\Admin\HydratorConfigurator;

use ExprAs\Rest\Hydrator\Configurator\AbstractRestHydratorConfigurator;
use ExprAs\Rest\Hydrator\RestHydrator;
use Laminas\Hydrator\Filter\FilterComposite;

class JsonApiFieldExcludeConfigurator extends AbstractRestHydratorConfigurator
{
    protected ?FilterComposite $filter = null;
    /**
     * JsonApiFieldExcludeConfigurator constructor.
     */
    public function __construct(protected string $entity, protected array $_fields)
    {
    }


    #[\Override]
    public function canConfigureFilters(RestHydrator $hydrator, object $object): bool
    {
        return $object instanceof $this->entity;
    }

    public function getFilter(): FilterComposite
    {
        if (!$this->filter) {
            $this->filter = new FilterComposite();
        }
        return $this->filter;

    }

    public function configureFilters(RestHydrator $_hydrator, object $object): void
    {
        if ($object instanceof $this->entity) {

            $name = 'exclude_fields_' . md5($object::class .serialize($this->_fields));
            //$this->getFilter()->addFilter($name, fn ($v) => !in_array($v, $this->_fields), FilterComposite::CONDITION_AND);


            $_hydrator->addFilter(
                $name,
                fn ($v) => !in_array($v, $this->_fields),
                FilterComposite::CONDITION_AND
            );
        }


    }
}
