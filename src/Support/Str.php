<?php

namespace Anteris\Model\Support;

/**
 * It did not make sense to ship with Laravel support dependencies, so our "studly"
 * method is ported here.
 */
class Str
{
    private static array $studlyCache = [];

    /**
     * Converts a string to studly caps case.
     *
     * @param  string  $value
     * @return string
     */
    public static function studly($value)
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return static::$studlyCache[$key] = str_replace(' ', '', $value);
    }
}
