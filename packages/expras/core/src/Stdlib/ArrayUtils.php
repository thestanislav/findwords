<?php

namespace ExprAs\Core\Stdlib;

class ArrayUtils
{
    /**
     * @param array        $array
     * @param array|string $parents
     * @param string       $glue
     *
     * @return mixed
     */
    static function getValue(array &$array, $parents, $glue = '.', $defaultValue = null): mixed
    {
        if (!is_array($parents)) {
            $parents = explode($glue, $parents);
        }

        $ref = &$array;

        foreach ((array)$parents as $parent) {
            if (is_array($ref) && array_key_exists($parent, $ref)) {
                $ref = &$ref[$parent];
            } else {
                return $defaultValue;
            }
        }
        return $ref;
    }

    /**
     * @param array        $array
     * @param array|string $parents
     * @param mixed        $value
     * @param string       $glue
     */
    static function setValue(array &$array, $parents, $value, $glue = '.'): void
    {
        if (!is_array($parents)) {
            $parents = explode($glue, (string)$parents);
        }

        $ref = &$array;

        foreach ($parents as $parent) {
            if (isset($ref) && !is_array($ref)) {
                $ref = [];
            }

            $ref = &$ref[$parent];
        }

        $ref = $value;
    }

    /**
     * @param array        $array
     * @param array|string $parents
     * @param string       $glue
     */
    static function unsetValue(&$array, $parents, $glue = '.'): void
    {
        if (!is_array($parents)) {
            $parents = explode($glue, $parents);
        }

        $key = array_shift($parents);

        if (empty($parents)) {
            unset($array[$key]);
        } else {
            self::unsetValue($array[$key], $parents);
        }
    }

    static function objectToArray($obj)
    {
        //only process if it's an object or array being passed to the function
        if (is_object($obj) || is_array($obj)) {
            $ret = (array)$obj;
            foreach ($ret as &$item) {
                //recursively process EACH element regardless of type
                $item = self::objectToArray($item);
            }
            return $ret;

            //otherwise (i.e. for scalar values) return without modification
        } else {
            return $obj;
        }
    }

    static function arrayToObject($array, $className = \stdClass::class)
    {
        $obj = new $className;

        foreach ($array as $k => $v) {
            if (strlen((string) $k)) {
                if (is_array($v)) {
                    $obj->{$k} = self::arrayToObject($v); //RECURSION
                } else {
                    $obj->{$k} = $v;
                }
            }
        }

        return $obj;
    }
}