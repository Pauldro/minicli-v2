<?php
namespace Pauldro\Minicli\v2\Cmd;

// PHP
use BadMethodCallException;
// Minicli
use Minicli\App as MinicliApp;
use Minicli\Command\CommandCall as MinicliCommandCall;
use Minicli\Command\CommandController;
use Minicli\Exception\MissingParametersException;
// Pauldro Minicli
use Pauldro\Minicli\v2\App\App;
use Pauldro\Minicli\v2\Logging\Logger;
use Pauldro\Minicli\v2\Output\OutputHandler as Printer;
use Pauldro\Minicli\v2\Util\Timer;
use Pauldro\UtilityBelt\Strings;
use Pauldro\UtilityBelt\SuperGlobals\EnvVarsReader as EnvVars;

/**
 * Template for Handling and Executing Commands
 *
 * @property CommandCall $input
 */
abstract class AbstractController extends CommandController
{
    public const DESCRIPTION = '';
    public const OPTIONS = [];
    public const NOTES = [];
    public const OPTIONS_DEFINITIONS = [];
    public const OPTIONS_DEFINITIONS_OVERRIDE = [];
    public const OPTIONS_SKIP = [];
    public const REQUIRED_PARAMS = [];
    public const SENSITIVE_PARAMS = [];
    /**  @var array<string,string> */
    public const REQUIRED_ENV_VARS = [];
    public const REQUIRED_PARAM_OR_GROUPS = [
        // 'credentials' => [
        //     'description' => 'FTP Credentials',
        //     'groups' => [
        //         'env-file' => ['env-ftp-credentials'],
        //         'params'   => ['server', 'login', 'password', 'port', '--use-ssl']
        //     ]
        // ]
    ];

    protected Printer $printer;
    protected Logger $log;
    protected Timer $timer;

    public function __construct()
    {
        $this->timer = new Timer();
    }

    /* =============================================================
           Init Functions
       ============================================================= */
    /**
     * Called before `run`
     *
     * @param  App $app
     * @param  CommandCall $input
     * @throws MissingParametersException
     */
    #[\Override]
    public function boot(MinicliApp $app, MinicliCommandCall $input): void
    {
        parent::boot($app, $input);
        $this->log = $app->log;
        $this->printer = $app->cliprinter;
    }

    /**
     * Setup controller
     * @param  App         $app
     * @param  CommandCall $input
     */
    public function bootstrap(MinicliApp $app, MinicliCommandCall $input): void
    {
        $this->app = $app;
        $this->config = $app->config;
        $this->logger = $app->logger;
        $this->log = $app->log;
        $this->printer = $app->cliprinter;
        $this->input = $input;
    }

    /**
     * The list of parameters required by the command.
     * @return array<int, string>
     */
    #[\Override]
    public function required(): array
    {
        return static::REQUIRED_PARAMS;
    }

    /**
     * Initialize App
     */
    protected function init(): bool
    {
        $this->initEnvTimeZone();
        if ($this->initIniDebug() === false) {
            return false;
        }
        if ($this->initRequiredEnvVars() === false) {
            return false;
        }
        return $this->initRequiredParams();
    }

    /**
     * Initialize the Local Time Zone
     */
    protected function initEnvTimeZone(): bool
    {
        $sysTZ = exec('date +%Z');
        $abbr = timezone_name_from_abbr($sysTZ);
        return date_default_timezone_set($abbr);
    }

    /**
     * Initialize php error logging, display of errors
     */
    protected function initIniDebug(): bool
    {
        if ($this->app->config->debug === false && $this->input->hasFlag('--debug') === false) {
            ini_set('display_errors', 'Off');
        }

        ini_set('log_errors', 'On');
        ini_set('error_log', $this->app->logs_path . '/php-errors.log');
        return true;
    }

    protected function initRequiredEnvVars(): bool
    {
        foreach (static::REQUIRED_ENV_VARS as $var => $description) {
            if (EnvVars::exists($var) === false) {
                return $this->error("Missing .env variable: $var - $description");
            }
        }
        return true;
    }

    /**
     * Initialize App
     */
    protected function initRequiredParams(): bool
    {
        foreach (static::REQUIRED_PARAMS as $param) {
            if ($this->hasParam($param) === false) {
                $description = array_key_exists($param, static::OPTIONS_DEFINITIONS) ? static::OPTIONS_DEFINITIONS[$param] : $param;
                $use = array_key_exists($param, static::OPTIONS) ? static::OPTIONS[$param] : '';
                return $this->error("Missing Parameter: $description ($use)");
            }
        }
        return true;
    }

    /* =============================================================
           Logging Functions
       ============================================================= */
    /**
     * Log Command sent to App
     * NOTE: Keep public so App can call it
     */
    public function logCommand(): bool
    {
        if (array_key_exists('LOG.COMMANDS', $_ENV) === false || $_ENV['LOG.COMMANDS'] == 'false') {
            return true;
        }
        $cmd = Logger::sanitizeCmdForLog($this->input, static::SENSITIVE_PARAMS);
        $this->log->command($cmd);
        return true;
    }

    /**
     * Log Command sent to App
     */
    protected function logError(string $msg): bool
    {
        if (array_key_exists('LOG.ERRORS', $_ENV) === false || $_ENV['LOG.ERRORS'] == 'false') {
            return true;
        }
        $cmd = Logger::sanitizeCmdForLog($this->input, static::SENSITIVE_PARAMS);
        $this->log->error(Logger::createLogString([$cmd, ' -> ', $msg]));
        return true;
    }

    /**
     * Log Error Message
     * @param  string $msg
     * @return false
     */
    protected function error($msg): bool
    {
        $this->printer->error($msg);
        $this->logError($msg);
        return false;
    }

    /* =============================================================
           Parameter Functions
       ============================================================= */
    /**
     * Return boolean value for parameter
     * @param  string $param Parameter to get Value from
     */
    protected function getParamBool($param): bool
    {
        return $this->input->getParamBool($param);
    }

    /**
     * Return Parameter Value
     * @param  string $param
     * @return string
     */
    #[\Override]
    protected function getParam($param): string|null
    {
        return $this->input->getParam($param);
    }

    /**
     * Return Parameter Value as array
     * @param  string $param	  Parameter Key
     * @param  string $delimiter  Delimiter
     */
    protected function getParamArray($param, $delimiter = ','): array
    {
        return $this->input->getParamArray($param, $delimiter);
    }

    /* =============================================================
           Displays
       ============================================================= */
    /**
     * Display Key Value Data
     */
    protected function displayDictionary(array $data): void
    {
        $printer = $this->printer;
        $titleLength = Strings::longestStrlen(array_keys($data));

        foreach ($data as $title => $value) {
            $lineData = [
                $printer->spaces(4),
                $printer->filterOutput(Strings::pad($title, $titleLength + 2), 'success'),
                $value
            ];
            $printer->line(implode('', $lineData));
        }
    }

    /* =============================================================
           Supplemental
       ============================================================= */
    /**
     * @param array<int,mixed> $arguments
     */
    #[\Override]
    public function __call(string $name, array $arguments): mixed
    {
        if (method_exists($this->printer, $name)) {
            return $this->printer->$name(...$arguments);
        }
        throw new BadMethodCallException("Method {$name} does not exist.");
    }
}
