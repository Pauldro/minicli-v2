<?php namespace Pauldro\Minicli\v2\Cmd;
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
use Pauldro\Minicli\v2\Util\StringUtilities as Strings;

/**
 * Template for Handling and Executing Commands
 *
 * @property CommandCall $input
 */
abstract class AbstractController extends CommandController {
	const DESCRIPTION = '';
	const OPTIONS = [];
	const NOTES = [];
	const OPTIONS_DEFINITIONS = [];
	const OPTIONS_DEFINITIONS_OVERRIDE = [];
	const REQUIRED_PARAMS = [];
	const SENSITIVE_PARAMS = [];

	protected Printer $printer;
	protected Logger $log;

/* =============================================================
	Init Functions
============================================================= */
	 /**
	 * Called before `run`
	 *
	 * @param  App $app
	 * @param  CommandCall $input
	 * @return void
	 * @throws MissingParametersException
	 */
	public function boot(MinicliApp $app, MinicliCommandCall $input) : void
	{
		parent::boot($app, $input);
		$this->log = $app->log;
		$this->printer = $app->cliprinter;
	}

	/**
	 * Setup controller
	 * @param  App $app
	 * @param  CommandCall $input
	 * @return void
	 */
	public function bootstrap(MinicliApp $app, MinicliCommandCall $input) : void
	{
		$this->app     = $app;
        $this->config  = $app->config;
        $this->logger  = $app->logger;
        $this->log     = $app->log;
		$this->printer = $app->cliprinter;
		$this->input   = $input;
	}

	/**
     * The list of parameters required by the command.
     *
     * @return array<int, string>
     */
    public function required() : array
    {
        return static::REQUIRED_PARAMS;
    }

	/**
	 * Initialize App
	 * @return bool
	 */
	protected function init() : bool
    {
		$this->initEnvTimeZone();

		if ($this->initRequiredParams() === false) {
			return false;
		}
		return true;
	}

	/**
	 * Initialize the Local Time Zone
	 * @return bool
	 */
	protected function initEnvTimeZone() : bool
    {
		$sysTZ = exec('date +%Z');
		$abbr = timezone_name_from_abbr($sysTZ);
		return date_default_timezone_set($abbr);
	}

	/**
	 * Initialize App
	 * @return bool
	 */
	protected function initRequiredParams() : bool
    {
		foreach (static::REQUIRED_PARAMS as $param) {
			if ($this->hasParam($param) === false) {
				$description = array_key_exists($param, static::OPTIONS_DEFINITIONS) ? static::OPTIONS_DEFINITIONS[$param] : $param;
				$use		 = array_key_exists($param, static::OPTIONS) ? static::OPTIONS[$param] : '';
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
	 * @return bool
	 */
	public function logCommand() : bool
    {
		if (array_key_exists('LOG.COMMANDS', $_ENV) === false || $_ENV['LOG.COMMANDS'] == 'false') {
			return true;
		}
		$cmd  = Logger::sanitizeCmdForLog($this->input, static::SENSITIVE_PARAMS);
		$this->log->info($cmd);
		return true;
	}

	/**
	 * Log Command sent to App
	 * @return bool
	 */
	protected function logError($msg) : bool
    {
		if (array_key_exists('LOG.ERRORS', $_ENV) === false || $_ENV['LOG.ERRORS'] == 'false') {
			return true;
		}
		$cmd  = Logger::sanitizeCmdForLog($this->input, static::SENSITIVE_PARAMS);
		$this->log->error(Logger::createLogString([$cmd, ' -> ', $msg]));
		return true;
	}

	/**
	 * Log Error Message
	 * @param  string $msg
	 * @return false
	 */
	protected function error($msg) : bool {
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
	 * @return bool
	 */
	protected function getParamBool($param) : bool
    {
		return $this->input->getParamBool($param);
	}

	/**
	 * Return Parameter Value
	 * @param  string $param
	 * @return string
	 */
	protected function getParam($param) : string|null
    {
		return $this->input->getParam($param);
	}

	/**
	 * Return Parameter Value as array
	 * @param  string $param	  Parameter Key
	 * @param  string $delimiter  Delimiter
	 * @return array
	 */
	protected function getParamArray($param, $delimiter = ",") : array
    {
		return $this->input->getParamArray($param, $delimiter);
	}

/* =============================================================
	Displays
============================================================= */
	/**
	 * Display Key Value Data
	 * @param  array $data
	 * @return void
	 */
	protected function displayDictionary(array $data) : void
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
     * @param string $name
     * @param array<int,mixed> $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (method_exists($this->printer, $name)) {
            return $this->printer->$name(...$arguments);
        }
        throw new BadMethodCallException("Method {$name} does not exist.");
    }
}
