<?php

namespace ExprAs\Rest\Handler;

use Laminas\Diactoros\ServerRequest;
use Laminas\Stdlib\Parameters;

/**
 * RequestParserTrait.php
 *
 * @link      http://github.com/asfor the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zucchi Limited (http://as.co.uk)
 * @license   http://as.co.uk/legals/bsd-license New BSD License
 * @author    Matt Cockayne <matt@as.co.uk>
 */
trait RequestParserTrait
{
    /**
     * Processes the WHERE clauses and operators provided in the request into
     * a format usable by the getList() method of the services.
     *
     * @return array
     */
    protected function _parseWhere(ServerRequest $request, $key = '_where')
    {
        $clauses = [
            'is'      => 'is',
            'in'      => 'in',
            'mr'      => 'member',
            'eq'      => '=',
            'gt'      => '>',
            'gte'     => '>=',
            'lt'      => '<',
            'lte'     => '<=',
            'neq'     => '!=',
            'between' => 'between',
            'fuzzy'   => 'like',
            'regex'   => 'regexp',
        ];


        // loop through and sanitize the where statement
        $where = [];


        foreach ((new Parameters($request->getQueryParams()))->get($key, []) as $field => $value) {
            if (is_array($value)) {
                if (isset($value['value']) && is_string($value['value'])) {
                    if (isset($value['operator']) && isset($clauses[$value['operator']])) {
                        $value['operator'] = $clauses[$value['operator']];
                    } else {
                        $value['operator'] = '=';
                    }
                    $where[$field] = $value;
                } elseif (!isset($value['value'])) {
                    $where[$field] = [
                        'operator' => 'in',
                        'value'    => $value
                    ];
                } else {
                    $where[$field] = $value;
                }
            } else {
                if (is_scalar($value)) {
                    $where[$field] = [
                        'operator' => '=',
                        'value'    => $value
                    ];
                }
            }
        }
        return $where;
    }
}
