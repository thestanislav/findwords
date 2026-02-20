<?php

namespace ExprAs\Doctrine\Query\Mysql\DataType;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use ExprAs\AdminGenerator\Mapping\Input\DateTime;

class Timestamp extends Type
{
    public function getName(): string
    {
        return 'timestamp';
    }

    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @param array                                     $column   The field declaration.
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform The currently used database platform.
     *
     * @return string
     * @throws \Doctrine\DBAL\Exception
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $name = $platform->getName();

        if (in_array($name, ['mysql', 'sqlite'])) {
            $method = 'get' . ucfirst($name) . 'PlatformSQLDeclaration';

            return $this->$method($column);
        }

        throw Exception::notSupported(__METHOD__);
    }

    /**
     * Gets the SQL declaration snippet for a field of this type for the MySQL Platform.
     *
     * @param array $fieldDeclaration The field declaration.
     *
     * @return string
     */
    protected function getMysqlPlatformSQLDeclaration(array $fieldDeclaration): string
    {
        $columnType = $fieldDeclaration['precision'] ? "TIMESTAMP({$fieldDeclaration['precision']})" : 'TIMESTAMP';

        if (isset($fieldDeclaration['notnull']) && $fieldDeclaration['notnull'] == true) {
            return $columnType;
        }

        return "$columnType NULL";
    }

    /**
     * Gets the SQL declaration snippet for a field of this type for the Sqlite Platform.
     *
     * @param array $fieldDeclaration The field declaration.
     *
     * @return string
     */
    protected function getSqlitePlatformSQLDeclaration(array $fieldDeclaration): string
    {
        return $this->getMysqlPlatformSQLDeclaration($fieldDeclaration);
    }

    /**
     * Converts a value from its PHP representation to its database representation
     * of this type.
     *
     * @param mixed            $value    The value to convert.
     * @param AbstractPlatform $platform The currently used database platform.
     *
     * @return mixed The database representation of the value.
     *
     * @throws ConversionException
     */
    #[\Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }
        return $value;
    }

    /**
     * Converts a value from its database representation to its PHP representation
     * of this type.
     *
     * @param mixed            $value    The value to convert.
     * @param AbstractPlatform $platform The currently used database platform.
     *
     * @return mixed The PHP representation of the value.
     *
     * @throws ConversionException
     */
    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $dt = new \DateTime();
        $dt->setTimestamp(intval($value));

        return $dt;
    }
}
