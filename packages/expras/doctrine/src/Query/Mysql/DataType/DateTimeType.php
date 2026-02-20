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
use Doctrine\DBAL\Types\DateTimeType as BaseDateTimeType;
use DateTime;

/**
 * Type that maps an SQL DATETIME/TIMESTAMP to an Extended PHP DateTime object.
 *
 * @author     Matt Cockayne <matt@zucchi.co.uk>
 * @package    ExprAs\Doctrine
 * @subpackage Datatype
 */
class DateTimeType extends BaseDateTimeType
{
    #[\Override]
    public function getName()
    {
        return Type::DATETIMETZ;
    }

    #[\Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return $platform->getDateTimeTypeDeclarationSQL($column);
    }

    #[\Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (is_null($value)) {
            return $value;
        }
        return ($value instanceof \DateTime)
            ? $value->format($platform->getDateTimeFormatString())
            : date($platform->getDateTimeFormatString(), strtotime((string) $value));
    }

    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value instanceof DateTime) {
            return $value;
        }

        $val = DateTime::createFromFormat($platform->getDateTimeFormatString(), $value);
        if (! $val) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), $platform->getDateTimeFormatString());
        }
        return $val;
    }
}
