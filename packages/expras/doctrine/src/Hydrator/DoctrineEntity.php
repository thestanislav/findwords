<?php
/**
 * Created by JetBrains PhpStorm.
 * User: stas
 * Date: 07.12.12
 * Time: 11:36
 * To change this template use File | Settings | File Templates.
 */

namespace ExprAs\Doctrine\Hydrator;

use Doctrine\Laminas\Hydrator\DoctrineObject as BaseDoctrineEntity;
use DateTime;
use DateTimeImmutable;

class DoctrineEntity extends BaseDoctrineEntity
{
    /**
     * Handle various type conversions that should be supported natively by Doctrine (like DateTime)
     * See Documentation of Doctrine Mapping Types for defaults
     *
     * @link http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/basic-mapping.html#doctrine-mapping-types
     *
     * @param mixed   $value
     * @param ?string $typeOfField
     *
     * @return mixed|null
     */
    #[\Override]
    protected function handleTypeConversions($value, $typeOfField)
    {
        if ($value === null) {
            return null;
        }

        switch ($typeOfField) {
        case 'boolean':
            $value = (bool) $value;
            break;
        case 'string':
        case 'text':
            $value = (string) $value;
            break;
        case 'integer':
        case 'smallint':
        case 'bigint':
        case 'decimal':
            $value = (int) $value;
            break;
        case 'float':
            $value = (float) $value;
            break;
        case 'datetimetz':
        case 'datetimetz_immutable':
        case 'datetime':
        case 'datetime_immutable':
        case 'time':
        case 'date':
            if ($value === '') {
                return null;
            }

            $isImmutable = str_ends_with((string) $typeOfField, 'immutable');

            // Psalm has troubles with nested conditions, therefore break this into two return statements.
            // See https://github.com/vimeo/psalm/issues/6683.
            if ($isImmutable && $value instanceof DateTimeImmutable) {
                return $value;
            }

            if (! $isImmutable && $value instanceof DateTime) {
                return $value;
            }

            if ($isImmutable && $value instanceof DateTime) {
                return DateTimeImmutable::createFromMutable($value);
            }

            if (! $isImmutable && $value instanceof DateTimeImmutable) {
                return DateTime::createFromImmutable($value);
            }

            if (is_int($value)) {
                $dateTime = $isImmutable
                    ? new DateTimeImmutable()
                    : new DateTime();

                return $dateTime->setTimestamp($value);
            }

            if (is_string($value)) {
                return $isImmutable
                    ? new DateTimeImmutable($value)
                    : new DateTime($value);
            }

            break;
        default:
            break;
        }

        return $value;
    }

}
