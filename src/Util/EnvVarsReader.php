<?php namespace Pauldro\Minicli\v2\Util;

/**
 * EnvVarsReader
 * 
 * Utility for reading values from $_ENV
 */
class EnvVarsReader {
    public function exists(string $key) : bool
    {
        return array_key_exists($key, $_ENV);
    }

    public function get(string $key, $default = '') : string
    {
        if ($this->exists($key) === false) {
            return $default;
        }
        return $_ENV[$key];
    }

    public function getBool(string $key) : bool
    {
        $value = $this->get($key, 'false');
        return $value == 'true';
    }

    public function getArray(string $key, $delimiter = ',') : array
    {
        return explode($delimiter, $this->get($key));
    }
}