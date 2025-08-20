<?php namespace Pauldro\Minicli\v2\Util;

/**
 * EnvVarsReader
 * 
 * Utility for reading values from $_ENV
 */
class EnvVarsReader {
    public static function exists(string $key) : bool
    {
        return array_key_exists($key, $_ENV);
    }

    public static function get(string $key, $default = '') : string
    {
        if (self::exists($key) === false) {
            return $default;
        }
        return $_ENV[$key];
    }

    public static function getBool(string $key) : bool
    {
        $value = self::get($key, 'false');
        return $value == 'true';
    }

    public static function getArray(string $key, $delimiter = ',') : array
    {
        return explode($delimiter, self::get($key));
    }
}