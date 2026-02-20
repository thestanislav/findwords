<?php
/**
 * ExprAs\Doctrine (http://zucchi.co.uk)
 *
 * @link      http://github.com/zucchi/ExprAs\Doctrine for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zucchi Limited. (http://zucchi.co.uk)
 * @license   http://zucchi.co.uk/legals/bsd-license New BSD License
 */

namespace ExprAs\Doctrine\Query\Mysql\DataType;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Type that works a a float value but can then be identified as monetary value
 *
 * @author     Matt Cockayne <matt@zucchi.co.uk>
 * @package    ExprAs\Doctrine
 * @subpackage Datatype
 */
class MoneyType extends Type
{
    public function getName()
    {
        return Type::FLOAT;
    }

    /**
     * @param array            $column
     * @param AbstractPlatform $platform
     *
     * @return string
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return $platform->getFloatDeclarationSQL($column);
    }

    /**
     * Converts a value from its database representation to its PHP representation
     * of this type.
     *
     * @param  mixed            $value    The value to convert.
     * @param  AbstractPlatform $platform The currently used database platform.
     * @return mixed The PHP representation of the value.
     */
    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return (null === $value) ? null : (float) $value;
    }
}
