<?php

namespace App\Lib;

use Archive\Exception;

class Sorting
{

    public static function validOrderMethod($method)
    {
        return in_array($method, ['name', 'time', 'size']);
    }

    public static function validOrderDirection($dir)
    {
        return in_array($dir, ['asc', 'desc']);
    }

    public static function sort(&$paths, $method, $direction)
    {
        if (!self::validOrderMethod($method) || !self::validOrderDirection($direction)) {
            throw new Exception('Invalid order params');
        }

        if ($method === 'name') {
            usort($paths, function ($a, $b) {
                return strnatcmp($a->name, $b->name);
            });
        } elseif ($method === 'time') {
            usort($paths, function ($a, $b) {
                if ($a->rawTime === $b->rawTime) {
                    return 0;
                }

                return ($a->rawTime > $b->rawTime) ? 1 : -1;
            });
        }
        if ($method === 'size') {
            usort($paths, function ($a, $b) {
                if ($a->rawSize === $b->rawSize) {
                    return 0;
                }

                return ($a->rawSize > $b->rawSize) ? 1 : -1;
            });
        }

        if ($direction === 'desc') {
            $paths = array_reverse($paths);
        }
    }
}
