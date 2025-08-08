<?php namespace Pauldro\Minicli\v2\Cmd;
// Minicli
use Minicli\App;
use Minicli\Command\CommandCall as MinicliCommandCall;
use Minicli\Command\CommandController;
use Minicli\Exception\MissingParametersException;
// Pauldro Minicli
use Pauldro\Minicli\v2\Logging\Logger;
use Pauldro\Minicli\v2\Output\OutputHandler as Printer;


/**
 * AbstractController
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
	const SENSITIVE_PARAM_VALUES = [];

	protected Printer $printer;
	protected Logger $log;

/* =============================================================
	Init Functions
============================================================= */
	 /**
	 * Called before `run`
	 *
	 * @param App $app
	 * @param CommandCall $input
	 * @return void
	 * @throws MissingParametersException
	 */
	public function boot(App $app, MinicliCommandCall $input) : void
	{
		parent::boot($app, $input);
		$this->log = $app->log;
		$this->printer = $app->cliprinter;
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
	 * Sanitize Command for Log Use
	 * @return string
	 */
	protected function sanitizeCmdForLog() : string
    {
		$cmd = implode(' ', $this->input->getRawArgs());

		foreach (static::SENSITIVE_PARAM_VALUES as $param) {
			$find = "$param=" . $this->getParam($param);
			$cmd = str_replace($find, "$param=***", $cmd);
		}
		return $cmd;
	}

	/**
	 * Log Command sent to App
	 * @return bool
	 */
	protected function logCommand() : bool
    {
		if (array_key_exists('LOG.COMMANDS', $_ENV) === false || $_ENV['LOG.COMMANDS'] == 'false') {
			return true;
		}
		$cmd  = $this->sanitizeCmdForLog();
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
		$cmd  = $this->sanitizeCmdForLog();
		$this->log->error($this->log->createLogString([$cmd, $msg]));
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
}
