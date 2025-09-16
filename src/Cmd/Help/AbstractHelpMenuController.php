<?php namespace Pauldro\Minicli\v2\Cmd\Help;
// Pauldro Minicli
use Pauldro\Minicli\v2\Util\StringUtilities as Strings;


/**
 * AbstractController
 * Handles Displaying the Help Menu
 */
abstract class AbstractHelpMenuController extends AbstractController  {
	const COMMAND_DEFINITIONS = [
		// '{{cmd}}' => '{{description}}',
	];

	protected $commandMap = [];

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

/* =============================================================
	Minicli Controller Contracts
============================================================= */
	public function handle() : void
    {
		$this->init();
		$this->intro();
		$this->display();
	}

/* =============================================================
	Display Printing
============================================================= */
	/**
	 * Return Default Display
	 * @return void
	 */
	protected function display() : void
    {
		$printer = $this->printer;
		$printer->line($printer->style('Available Commands:', 'info_header'));
		$this->displayCommands();
		$printer->newline();
		$printer->newline();
	}

	/**
	 * Display Commands and their Subcommands
	 * @return void
	 */
	protected function displayCommands() : void
    {
		$cmdLength  = $this->getLongestCommandSubcommandLength() + 4;
		
		foreach ($this->commandMap as $command => $subcommands) {
			if ($command == 'test' || $command == 'help') {
				continue;
			}

			if (is_array($subcommands) === false) {
				$subcommands = [];
			}
			$this->displayCommand($cmdLength, $command, $subcommands);
		}

        if (array_key_exists('help', $this->commandMap) === false) {
            return;
        }
		$this->displayCommand($cmdLength, 'help', $this->commandMap['help']);
	}

	/**
	 * Display Command Defintion along with subcommands
	 * @param  int    $cmdLength
	 * @param  string $command
	 * @param  array  $subcommands
	 * @return void
	 */
	protected function displayCommand($cmdLength, $command, $subcommands = []) : void
    {
		$printer    = $this->printer;
		$this->displayCommandDefinition($cmdLength, $command);

		foreach ($subcommands as $subcommand) {
			if ($subcommand == 'default') {
				continue;
			}
			$this->displayCommandDefinition($cmdLength, $command, $subcommand);
		}
		$printer->newline();
		return;
	}

	/**
	 * Display Command Defintion
	 * @param  int    $cmdLength
	 * @param  string $command
	 * @param  string $subcommand
	 * @return void
	 */
	protected function displayCommandDefinition($cmdLength, $command, $subcommand = 'default') : void
    {
		$printer = $this->printer;
		$handler = $this->getApp()->commandRegistry->getCallableController($command, $subcommand);

        if (empty($handler)) {
			if ($subcommand != 'default') {
				return;
			}
			$printer->newline();
			$line = $printer->style(Strings::pad($command, $cmdLength), 'info');
			$printer->out($line, false);
            return;
        }

        $printer->newline();

		if ($subcommand == 'default') {
			$line = sprintf('%s%s', $printer->style(Strings::pad($command, $cmdLength), 'info'), $handler::DESCRIPTION);
			$printer->out($line, false);
			return;
		}
		$cmd = $printer->spaces(2) . $subcommand;
		$line = sprintf('%s%s', $printer->style(Strings::pad($cmd, $cmdLength), 'info'), $handler::DESCRIPTION);
		$printer->out($line, false);
		return;
	}

	/**
	 * Display Intro
	 * @return void
	 */
	protected function intro() : void {
		$printer = $this->printer;
		$printer->out($this->app->config->app_name, 'italic');
		$printer->newline();
		$printer->line($this->app->config->app_description);
		$printer->newline();
        return;
	}
	
/* =============================================================
	Supplemental
============================================================= */
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
}
