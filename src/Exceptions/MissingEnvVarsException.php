<?php namespace Pauldro\Minicli\v2\Exceptions;
// Dotenv
use Dotenv\Exception\ValidationException;
// Pauldro
use Pauldro\UtilityBelt\Exceptions\MissingEnvVarsException as ParentException;

class MissingEnvVarsException extends ParentException {
    /**
     * Parse Missing Variables
     * @param  ValidationException $e
     * @return void
     */
    public function parseVarsFromValidationException(ValidationException $e) : void
    {
        $pieces = explode(':', $e->getMessage());
        $msg = '.env required failure: ' . trim($pieces[1]);
        $vars = [];

        foreach (explode(',', $msg) as $part) {
            $var = rtrim($part, ' is missing');
            $var = trim($var);
            $vars[] = $var;
        }
        $this->vars = $vars;
    }
}
