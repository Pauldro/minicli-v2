<?php namespace Pauldro\Minicli\v2\Cmd\Help;


/**
 * AbstractController
 * Handles Displaying the Help Menu
 */
abstract class AbstractHelpMenuController extends AbstractController  {
	const COMMAND_DEFINITIONS = [
		// '{{cmd}}' => '{{description}}',
	];

	public function handle() : void
    {
		$this->init();
		$this->intro();
		$this->display();
	}

	/**
	 * Return Default Display
	 * @return void
	 */
	protected function display() : void
    {
		$printer = $this->printer;
        $printer = $this->getApp()->getPrinter();
		$printer->info('Available Commands:');
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
	 * @param  int $cmdLength
	 * @param  string $command
	 * @param  string $subcommand
	 * @return void
	 */
	protected function displayCommandDefinition($cmdLength, $command, $subcommand = 'default') : void
    {
		$printer = $this->printer;
		$handler = $this->getApp()->commandRegistry->getCallableController($command, $subcommand);

        if (empty($handler)) {
            return;
        }

        $printer->newline();

		if ($subcommand == 'default') {
			$line = sprintf('%s%s', $printer->out($this->getCommandToLength($command, $cmdLength), 'info'), $handler::DESCRIPTION);
			$printer->line($line, false);
			return;
		}
		$cmd = $printer->spaces(2) . $subcommand;
		$line = sprintf('%s%s', $printer->out($this->getCommandToLength($cmd, $cmdLength), 'info'), $handler::DESCRIPTION);
		$printer->line($line, false);
		return;
	}

	/**
	 * Display Intro
	 * @return void
	 */
	protected function intro() : void {
		$printer = $this->printer;
		$printer->line(static::INTRO_DELIMITER);
		$printer->line('/ ' . $this->getCommandToLength("{$this->app->config->app_name}:", strlen(static::INTRO_DELIMITER) - 4) . ' /');
		$printer->line('/ ' . $this->getCommandToLength("{$this->app->config->app_description}", strlen(static::INTRO_DELIMITER) - 4) . ' /');
		$printer->line(static::INTRO_DELIMITER);
		$printer->newline();
        return;
	}
}
