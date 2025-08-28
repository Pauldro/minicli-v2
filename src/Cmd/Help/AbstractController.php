<?php namespace Pauldro\Minicli\v2\Cmd\Help;
// PHP Core
use ReflectionClass;
// Pauldro Minicli
use Pauldro\Minicli\v2\Cmd\AbstractController as ParentController;
use Pauldro\Minicli\v2\Util\StringUtilities as Strings;


/**
 * AbstractController
 * Handles Displaying the Help Screen for a Command
 *
 * @property array $commandMap Array of Map Commands
 */
abstract class AbstractController extends ParentController {
	const COMMAND = '';
	const DESCRIPTION = '';
	const COMMAND_DEFINITIONS = [];
	const OPTIONS = [];
	const OPTIONS_DEFINITIONS = [];
	const OPTIONS_DEFINITIONS_OVERRIDE = [];
	const SUBCOMMANDS = [];
	const NOTES = [];
	const INTRO_DELIMITER = '/////////////////////////////////////////////////////////';
	const REQUIRED_PARAMS = [];

	protected $commandMap = [];

	public function handle() : void
    {
		$this->display();
	}

	/**
     * The list of parameters required by the command.
     *
     * @return array<int, string>
     */
    public function required() : array
    {
        return [];
    }

	/**
	 * Display Command
	 * @return void
	 */
	protected function display() : void
    {
		$this->displayUsage();
		$this->displayOptions();
		$this->displayRequiredParams();
		$this->displayHelp();
		$this->displaySubcommands();
		$this->displayNotes();

		$printer = $this->printer;
		$printer->newline();
		$printer->newline();
	}

	/**
	 * Display Command Usage
	 * @return void
	 */
	protected function displayUsage() : void
    {
		$printer = $this->printer;
		$printer->line($printer->style('Usage:', 'info_header'));
		$script = $this->app->getSignature();
		$printer->line(sprintf('%s%s%s', $printer->style($script, 'italic'), $printer->spaces(1), $printer->style(static::COMMAND, 'info') . ' [options]'));
		$printer->newline();
		return;
	}

	/**
	 * Display Command Options
	 * @return void
	 */
	protected function displayOptions() : void
    {
		$printer = $this->printer;
		$optLength = $this->getLongestOptExampleLength() + 4;
		$printer->line($printer->style('Options:', 'info_header'));

		foreach (static::OPTIONS as $option => $example) {
			$printer->line(sprintf('%s%s%s', $printer->spaces(2), Strings::pad($example, $optLength), $this->getOptDefinition($option)));
		}
		return;
	}

	/**
	 * Display Required Command Parameters
	 * @return void
	 */
	protected function displayRequiredParams() : void
    {
		if (empty(static::REQUIRED_PARAMS)) {
			return;
		}

		$printer = $this->printer;
		$optLength = $this->getLongestOptExampleLength() + 4;
		$printer->line($printer->style('Required:', 'info_header'));

		foreach (static::REQUIRED_PARAMS as $option) {
			if (array_key_exists($option, static::OPTIONS) === false) {
				continue;
			}
			
			$example = Strings::pad($option, $optLength);
			$description = $this->getOptDefinition($option);
			$printer->line(sprintf('%s%s%s', $printer->spaces(2), $example, $description));
		}
		return;
	}

	/**
	 * Display Command Help
	 * @return void
	 */
	protected function displayHelp() : void
    {
		$printer = $this->printer;
		$printer->line($printer->style('Help:', 'info_header'));
		$printer->line(sprintf('%s%s', $printer->spaces(2), static::DESCRIPTION));
		return;
	}

	/**
	 * Display Notes
	 * @return void
	 */
	protected function displayNotes() : void
    {
		$printer = $this->printer;

		if (empty(static::NOTES) === false) {
			$printer->line($printer->style('Notes:', 'info_header'));
		}

		foreach (static::NOTES as $line) {
			$printer->line(sprintf('%s%s%s', $printer->spaces(2), ' ', $line));
		}
		return;
	}


	/**
	 * Display Subcommands
	 * @return void
	 */
	protected function displaySubcommands() : void
    {
		$printer = $this->printer;

        if (empty(static::SUBCOMMANDS)) {
            return;
        }
		$printer->line($printer->style('See Also:', 'info_header'));
		

		foreach (static::SUBCOMMANDS as $cmd) {
			$printer->line(sprintf('%s%s%s%s%s', $printer->spaces(2), 'help ', static::COMMAND, ' ', $cmd));
		}
		return;
	}

	/**
	 * Display Subcommand
     * TODO: update finding sub controller
	 * @return bool
	 */
	protected function displaySubcommand() : void
    {
		if (in_array($this->input->lastArg(), static::SUBCOMMANDS) === false) {
			return;
		}
		$reflector = new ReflectionClass(get_class($this));
		$baseNs = $reflector->getNamespaceName();
		$ns = $baseNs . '\\' . ucfirst(static::COMMAND) . '\\';
		$class = $ns . ucfirst($this->input->lastArg()) . 'Controller';
		if (class_exists($class) === false) {
			$this->error("Controller not found: $class");
            return;
		}
		/**
		 * @var AbstractController
		 */
		$handler = new $class();
		$handler->boot($this->app, $this->input);
		$handler->handle();
		return;
	}

	/**
	 * Return String Length of Longest Command
	 * @return int
	 */
	protected function getLongestOptLength() : int
    {
		return Strings::longestStrlen(array_keys(static::OPTIONS_DEFINITIONS)); 
	}

	/**
	 * Return String Length of Longest Command
	 * @return int
	 */
	protected function getLongestOptExampleLength() : int
    {
		return Strings::longestStrlen(array_values(static::OPTIONS));
	}

	/**
	 * Return Argument Defination
	 * @param  string $opt Option, Argument (param|flag)
	 * @return string
	 */
	protected function getOptDefinition($opt) : string
    {
		if (array_key_exists($opt, static::OPTIONS_DEFINITIONS_OVERRIDE)) {
			return static::OPTIONS_DEFINITIONS_OVERRIDE[$opt];
		}
		if (array_key_exists($opt, static::OPTIONS_DEFINITIONS) === false) {
			return '';
		}
		return static::OPTIONS_DEFINITIONS[$opt];
	}

	/**
	 * Return String Length of Longest Command
	 * @return int
	 */
	protected function getLongestCommandLength() : int
    {
		return Strings::longestStrlen(array_keys(static::COMMAND_DEFINITIONS));
	}

	/**
	 * Return the Longest Command / Subcommand length
	 * @return int
	 */
	protected function getLongestCommandSubcommandLength() : int
    {
		$list = [];

		foreach ($this->commandMap as $command => $subcommands) {
			$list[] = $command;

			if (is_array($subcommands) === false) {
				continue;
			}

			foreach ($subcommands as $subcommand) {
				$cmd = '  ' . $subcommand;
				$list[] = $cmd;
			}
		}
		return Strings::longestStrlen($list);
	}

	/**
	 * Return Definition of Command if Definition Exists
	 * @param  string $cmd Command
	 * @return string
	 */
	public function getCommandDefinition($cmd) : string
    {
		if (array_key_exists($cmd, static::COMMAND_DEFINITIONS) === false) {
			return '';
		}
		return static::COMMAND_DEFINITIONS[$cmd];
	}

/* =============================================================
	Init Functions
============================================================= */
	/**
	 * Initialize App
	 * @return bool
	 */
	protected function init() : bool
    {
		return $this->initCommandMap();
	}

	/**
	 * Initialize Command Map
	 * @return bool
	 */
	protected function initCommandMap() : bool
    {
		$this->commandMap = $this->getApp()->commandRegistry->getCommandMap();
		return true;
	}
}
