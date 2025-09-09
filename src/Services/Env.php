<?php namespace Pauldro\Minicli\v2\Services;
use Exception;
// DotEnv
use Dotenv\Dotenv;
use Dotenv\Exception\ValidationException;
// Minicli
use Minicli\App;
use Minicli\ServiceInterface;
// Pauldro Minicli
use Pauldro\Minicli\v2\Exceptions\MissingEnvVarsException;
use Pauldro\Minicli\v2\Util\EnvVarsReader as EnvVars;


/**
 * Wrapper for Dotenv for environment variables
 */
class Env implements ServiceInterface {
    const REQUIRED = [];
    protected string $dir;
    protected string $filepath;
    protected Dotenv $env;

    /**
     * load
     * @param  App  $app
     * @throws Exception
     * @return void
     */
    public function load(App $app) : void
    {
        $this->dir = $app->base_path;
        try {
            $dotenv = Dotenv::createImmutable($this->dir);
            $dotenv->load();
        } catch (Exception $e) {
            throw new Exception("Unable to load app .env");
        }
        $this->filepath = $this->dir . '.env';
        $this->env = $dotenv;
        $this->env->required(static::REQUIRED);
    }

    /**
     * Return if required variables are set
     * @param  array $vars
     * @throws MissingEnvVarsException
     * @return bool
     */
    public function required(array $vars) : bool
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
     * Return if variable is set
     * @param  string $var
     * @return bool
     */
    public function exists(string $var) : bool
    {
        try {
            $this->env->required($var);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Return value
     * @param  string $var
     * @return string|null
     */
    public function get(string $var) : string|null
    {
        if ($this->exists($var) === false) {
            return null;
        }
        return EnvVars::get($var);
    }

    /**
     * Return value as a boolean
     * @param  string $var
     * @return bool
     */
    public function getBool(string $var) : bool
    {
        if ($this->exists($var) === false) {
            return false;
        }
        return  EnvVars::getBool($var);
    }
}