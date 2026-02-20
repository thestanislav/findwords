<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 11/9/2017
 * Time: 14:55
 */

use ExprAs\Doctrine\Behavior\Aggregatable\AggregatableListener;
use ExprAs\Doctrine\Behavior\FieldAudit\FieldAuditListener;
use ExprAs\Doctrine\Behavior\Singleable\SingleableListener;
use ExprAs\Doctrine\Behavior\Ratable\RatableListener;
use ExprAs\Doctrine\Query\Mysql\DataType\Timestamp;
use ExprAs\Doctrine\Query\Mysql\Func\Greatest;
use ExprAs\Doctrine\Repository\RepositoryFactory;
use Gedmo\SoftDeleteable\SoftDeleteableListener;
use Gedmo\Sortable\SortableListener;
use Gedmo\Timestampable\TimestampableListener;
use ExprAs\Doctrine\Query\Mysql\DataType\DateTimeMs;

return [
    'doctrine' => [
        'configuration' => [
            'orm_default' => [
                'string_functions'   => [
                    'REGEXP'      => \ExprAs\Doctrine\Query\Mysql\Func\Regexp::class,
                    'CHAR_LENGTH' => \ExprAs\Doctrine\Query\Mysql\Func\CharLength::class,
                    'YEAR'        => \ExprAs\Doctrine\Query\Mysql\Func\Year::class,
                    'DATEDIFF'    => \ExprAs\Doctrine\Query\Mysql\Func\DateDiff::class,
                    'FIND_IN_SET' => \ExprAs\Doctrine\Query\Mysql\Func\FindInSet::class,
                    'FIELD'       => \ExprAs\Doctrine\Query\Mysql\Func\Field::class,
                    'IFELSE'      => \ExprAs\Doctrine\Query\Mysql\Func\IfElse::class,
                    'IFNULL'      => \ExprAs\Doctrine\Query\Mysql\Func\IfNull::class,
                    'MD5'         => \ExprAs\Doctrine\Query\Mysql\Func\Md5::class,
                    'ConcatWs'    => \ExprAs\Doctrine\Query\Mysql\Func\ConcatWs::class
                ],
                'numeric_functions'  => [
                    'RAND'            => \ExprAs\Doctrine\Query\Mysql\Func\Rand::class,
                    'ROUND'           => \ExprAs\Doctrine\Query\Mysql\Func\Round::class,
                    'ACOS'            => \ExprAs\Doctrine\Query\Mysql\Func\Acos::class,
                    'ASIN'            => \ExprAs\Doctrine\Query\Mysql\Func\Asin::class,
                    'ATAN'            => \ExprAs\Doctrine\Query\Mysql\Func\Atan::class,
                    'ATAN2'           => \ExprAs\Doctrine\Query\Mysql\Func\Atan2::class,
                    'MONTH'           => \ExprAs\Doctrine\Query\Mysql\Func\Month::class,
                    'WEEK'            => \ExprAs\Doctrine\Query\Mysql\Func\Week::class,
                    'DAY'             => \ExprAs\Doctrine\Query\Mysql\Func\Day::class,
                    'DAY_OF_WEEK'     => \ExprAs\Doctrine\Query\Mysql\Func\DayOfWeek::class,
                    'YEAR'            => \ExprAs\Doctrine\Query\Mysql\Func\Year::class,
                    'DATE'            => \ExprAs\Doctrine\Query\Mysql\Func\Date::class,
                    'TIME'            => \ExprAs\Doctrine\Query\Mysql\Func\Time::class,
                    'CAST_AS_INTEGER' => \ExprAs\Doctrine\Query\Mysql\Func\CastAsInteger::class,
                    'GREATEST'        => Greatest::class
                ],
                'datetime_functions' => ['DATE_FORMAT' => \ExprAs\Doctrine\Query\Mysql\Func\DateFormat::class],
                'types'              => [
                    //'datetime' => 'ExprAs\Doctrine\Query\Mysql\DataType\DateTimeType',
                    //'date' => 'ExprAs\Doctrine\Query\Mysql\DataType\DateType',
                    //'time' => 'ExprAs\Doctrine\Query\Mysql\DataType\TimeType',
                    'money'           => \ExprAs\Doctrine\Query\Mysql\DataType\MoneyType::class,
                    'datetimems'      => DateTimeMs::class,
                    DateTimeMs::class => DateTimeMs::class,
                    'timestamp'       => Timestamp::class,
                    Timestamp::class  => Timestamp::class,
                ],
                'filters'            => [
                    'soft-deleteable' => \Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter::class,
                    'activatable'     => \ExprAs\Doctrine\Behavior\Activatable\ActivatableFilter::class
                ],
                'repository_factory' => RepositoryFactory::class,
                'metadata_cache'     => 'default',
                'query_cache'        => 'default',
                'result_cache'       => 'default',
                'hydration_cache'    => 'default'
            ]
        ],
        'connection'    => [
            'orm_default' =>
                [
                    'doctrine_type_mappings' =>
                        [
                            'money' => 'money',
                            'enum'  => 'string'
                        ]
                ]
        ],
        'eventmanager'  => [
            'orm_default' => [
                'subscribers' => [
                    SoftDeleteableListener::class,
                    TimestampableListener::class,
                    SortableListener::class,
                    SingleableListener::class,
                    [
                        'subscriber' => AggregatableListener::class,
                        'priority' => 1000 - PHP_INT_MAX
                    ],
                    RatableListener::class,
                    FieldAuditListener::class
                ]
            ]
        ]
    ],

];
