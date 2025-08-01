<?php

namespace App\Helpers;

class NumberHelper
{

    public static function isCastableToInt(mixed $value): bool
    {
        if (is_int($value)) {
            return true;
        }

        if (is_string($value)) {
            return preg_match('/^\s*[+-]?\d+\s*$/', $value) === 1;
        }

        return false;
    }


    public static function isCastableToFloat(mixed $value): bool
    {
        if (is_float($value) || is_int($value)) {
            return true;
        }

        if (is_string($value)) {
            return preg_match('/^\s*[+-]?(?:\d+(\.\d*)?|\.\d+)([eE][+-]?\d+)?\s*$/', $value) === 1;
        }

        return false;
    }
}
