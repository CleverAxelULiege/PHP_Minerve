<?php

namespace App\Support;

class Query
{
    public static function getParameter(string $key, mixed $default = null)
    {
        return $_GET[$key] ?? $default;
    }
}
