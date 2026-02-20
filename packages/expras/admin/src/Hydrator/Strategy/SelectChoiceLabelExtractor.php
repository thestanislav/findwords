<?php

namespace ExprAs\Admin\Hydrator\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;

class SelectChoiceLabelExtractor implements StrategyInterface
{
    protected $_choices = [];

    /**
     * SelectChoiceLabelExtractor constructor.
     *
     * @param array $_choices
     */
    public function __construct(array $choices)
    {
        $this->_choices = $choices;
    }


    /**
     * Converts the given value so that it can be extracted by the hydrator.
     *
     * @param  mixed       $value  The original value.
     * @param  null|object $object (optional) The original object for context.
     * @return mixed       Returns the value that should be extracted.
     */
    public function extract($value, ?object $object = null)
    {
        if (array_key_exists($value, $this->_choices)) {
            return $this->_choices[$value];
        }
        return null;
    }



    public function hydrate($value, ?array $data)
    {
        return $value;
    }
}
