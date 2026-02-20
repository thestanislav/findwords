<?php

namespace ExprAs\Rest\Hydrator\Strategy;

use Laminas\Hydrator\Strategy\StrategyInterface;

class DateTimeFormatterStrategy implements StrategyInterface
{
    protected $_format = 'c';

    /**
     * @return $this
     */
    public function setFormat(string $format): void
    {
        $this->_format = $format;
        return;
        $this;
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
        if ($value instanceof \DateTimeInterface) {
            return $value->format($this->_format);
        }
        return $value;
    }

    /**
     * Converts the given value so that it can be hydrated by the hydrator.
     *
     * @param  mixed      $value The original value.
     * @param  null|array $data  The original data for context.
     * @return mixed      Returns the value that should be hydrated.
     */
    public function hydrate($value, ?array $data)
    {
        return $value;
    }
}
