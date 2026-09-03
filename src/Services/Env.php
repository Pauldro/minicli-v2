<?php
namespace Pauldro\Minicli\v2\Services;

use Exception;
// DotEnv
use Dotenv\Dotenv;
use Dotenv\Exception\ValidationException;
// Minicli
use Minicli\App;
use Minicli\ServiceInterface;
// Pauldro
use Pauldro\Minicli\v2\Exceptions\MissingEnvVarsException;
use Pauldro\UtilityBelt\SuperGlobals\EnvVarsReader as EnvVars;

/**
 * Wrapper for Dotenv for environment variables for the main .env file
 */
class Env implements ServiceInterface
{
    public const REQUIRED = [];
    protected string $dir;
    protected string $filepath;
    protected Dotenv $env;

    /**
     * load
     * @throws Exception
     */
    public function load(App $app): void
    {
        $this->dir = rtrim($app->base_path, '/') . '/';
        try {
            $dotenv = Dotenv::createImmutable($this->dir);
            $dotenv->load();
        } catch (Exception) {
            throw new Exception('Unable to load app .env');
        }
        $this->filepath = $this->dir . '.env';
        $this->env = $dotenv;
        $this->env->required(static::REQUIRED);

        if (EnvVars::getBool('APP.DEBUG')) {
            $app->config->debug = true;
        }
    }

    /**
     * Return if required variables are set
     * @throws MissingEnvVarsException
     */
    public function required(array $vars): bool
    {
        try {
            $this->env->required($vars);
        } catch (ValidationException $e) {
            $exception = new MissingEnvVarsException($e->getMessage());
            $exception->parseVarsFromValidationException($e);
            $exception->setFilepath($this->filepath);
            $exception->generateMessage();
            throw $exception;
        }
        return true;
    }

    /**
     * Return if required variables are set
     * @throws MissingEnvVarsException
     */
    public function requiredPrefixed(array $vars, string $prefix = ''): bool
    {
        if ($prefix === '' || $prefix === '0') {
            return $this->required($vars);
        }
        $newVars = [];

        foreach ($vars as $var) {
            $newVars[] = "$prefix.$var";
        }
        return $this->required($newVars);
    }

    /**
     * Return if variable is set
     */
    public function exists(string $var): bool
    {
        try {
            $this->env->required($var);
        } catch (Exception) {
            return false;
        }
        return true;
    }

    /**
     * Return value
     */
    public function get(string $var): string
    {
        if ($this->exists($var) === false) {
            return '';
        }
        return EnvVars::get($var);
    }

    /**
     * Return value as a boolean
     */
    public function getBool(string $var): bool
    {
        if ($this->exists($var) === false) {
            return false;
        }
        return  EnvVars::getBool($var);
    }
}
