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
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

/**
 * Type that maps an SQL DATETIME/TIMESTAMP to an Extended PHP DateTime object.
 *
 * @author     Matt Cockayne <matt@zucchi.co.uk>
 * @package    ExprAs\Doctrine
 * @subpackage Datatype
 */
class DateType extends Type
{
    public function getName()
    {
        return Type::DATE;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return $platform->getDateTypeDeclarationSQL($column);
    }

    #[\Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return ($value !== null)
            ? $value->format($platform->getDateFormatString()) : null;
    }

    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value instanceof \DateTime) {
            return $value;
        }

        $val = \DateTime::createFromFormat('!'.$platform->getDateFormatString(), $value);
        if (! $val) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), $platform->getDateFormatString());
        }
        return $val;
    }
}
