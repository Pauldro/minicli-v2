<?php namespace Pauldro\Minicli\v2\Util;
// Pauldro Minicli
use Pauldro\Minicli\v2\Exceptions\MissingEnvVarsException;

/**
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

    /**
     * Validate Required variables are set
     * @param  array $vars
     * @throws MissingEnvVarsException
     * @return bool
     */
    public static function required(array $vars) : bool
    {
        $missing = new SimpleArray();

        foreach ($vars as $var) {
            if (self::exists($var)) {
                continue;
            }
            $missing->add($var);
        }
        if ($missing->count() == 0) {
            return true;
        }
        $e = new MissingEnvVarsException();
        $e->setVars($missing->getArray());
        $e->generateMessage();
        throw $e;
    }

    /**
     * Validate Required variables are set
     * @param  array  $vars
     * @param  string $prefix
     * @throws MissingEnvVarsException
     * @return bool
     */
    public static function requiredPrefixed(array $vars, string $prefix = '') : bool
    {
        if (empty($prefix)) {
            return self::required($vars);
        }
        $newVars = [];

        foreach ($vars as $var) {
            $newVars[] = "$prefix.$var";
        }
        return self::required($newVars);
    }
}