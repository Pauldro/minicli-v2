<?php
namespace Pauldro\Minicli\v2\Cmd\Help;

// Pauldro
use Pauldro\Minicli\v2\Cmd\AbstractController as AbstractCmdController;
use Pauldro\Minicli\v2\Cmd\Help\AbstractController as AbstractCmdHelpController;
use Pauldro\UtilityBelt\Strings;


/**
 * Handles Displaying the Help Menu
 */
abstract class AbstractHelpMenuController extends AbstractController
{
    public const COMMAND_DEFINITIONS = [
        // '{{cmd}}' => '{{description}}',
    ];

    protected $commandMap = [];

    /* =============================================================
           Init Functions
       ============================================================= */
    /**
     * Initialize App
     */
    #[\Override]
    protected function init(): bool
    {
        return $this->initCommandMap();
    }

    /**
     * Initialize Command Map
     */
    protected function initCommandMap(): bool
    {
        $this->commandMap = $this->getApp()->commandRegistry->getCommandMap();
        return true;
    }

    /* =============================================================
        Minicli Controller Contracts
    ============================================================= */
    #[\Override]
    public function handle(): void
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
     */
    #[\Override]
    protected function display(): void
    {
        $printer = $this->printer;
        $printer->line($printer->style('Available Commands:', 'info_header'));
        $this->displayCommands();
        $printer->newline();
        $printer->newline();
    }

    /**
     * Display Commands and their Subcommands
     */
    protected function displayCommands(): void
    {
        $cmdLength = $this->getLongestCommandSubcommandLength() + 4;

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
        $this->displayHelpCommand($cmdLength);
    }

    /**
     * Display Command Defintion along with subcommands
     * @param  int    $cmdLength
     * @param  string $command
     * @param  array  $subcommands
     */
    protected function displayCommand($cmdLength, $command, $subcommands = []): void
    {
        $printer = $this->printer;
        $this->displayCommandDefinition($cmdLength, $command);

        foreach ($subcommands as $subcommand) {
            if ($subcommand == 'default') {
                continue;
            }
            $this->displayCommandDefinition($cmdLength, $command, $subcommand);
        }
        $printer->newline();
    }

    /**
     * Display Command Defintion
     * @param  int    $cmdLength
     * @param  string $command
     * @param  string $subcommand
     */
    protected function displayCommandDefinition($cmdLength, $command, $subcommand = 'default'): void
    {
        $printer = $this->printer;
        /** @var AbstractCmdController */
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
            $line = sprintf('%s%s', $printer->style(Strings::pad($handler->getCmdCallName(), $cmdLength), 'info'), $handler::DESCRIPTION);
            $printer->out($line, false);
            return;
        }
        $cmd = $printer->spaces(2) . $handler->getCmdCallName();
        $line = sprintf('%s%s', $printer->style(Strings::pad($cmd, $cmdLength), 'info'), $handler::DESCRIPTION);
        $printer->out($line, false);
    }

    /**
     * Display Intro
     */
    protected function intro(): void
    {
        $printer = $this->printer;
        $printer->out($this->app->config->app_name, 'italic');
        $printer->newline();
        $printer->line($this->app->config->app_description);
        $printer->newline();
    }

    /**
     * Display Help Command Defintion along with subcommands
     * @param  int    $cmdLength
     * @return void
     */
    protected function displayHelpCommand(int $cmdLength) : void
    {
        $printer    = $this->printer;
        $command = 'help';
        $subcommands = $this->commandMap['help'];

        $this->displayCommandDefinition($cmdLength, $command);
        

        foreach ($subcommands as $subcommand) {
            if ($subcommand == 'default') {
                continue;
            }
            $this->displayCommandDefinition($cmdLength, $command, $subcommand);
            $printer->newline();
            
            /** @var AbstractCmdHelpController */
            $handler = $this->app->commandRegistry->getCallableController($command, $subcommand);

            if (empty($handler::SUBCOMMANDS)) {
                continue;
            }

            foreach ($handler::SUBCOMMANDS as $subcmd) {
                /** @var AbstractCmdHelpController */
                $helpHandler = $this->app->commandRegistry->getCallableController("$command $subcommand", $subcmd);
                
                if (empty($helpHandler)) {
                    continue;
                }
                $cmd = $printer->spaces(4) . $subcmd;
                $line = sprintf('%s%s', $printer->style(Strings::pad($cmd, $cmdLength), 'info'), $helpHandler::DESCRIPTION);
                $printer->out($line, false);
                $printer->newline();
            }
        }
        return;
    }

    /* =============================================================
           Supplemental
       ============================================================= */
    /**
     * Return String Length of Longest Command
     */
    protected function getLongestCommandLength(): int
    {
        return Strings::longestStrlen(array_keys(static::COMMAND_DEFINITIONS));
    }

    /**
     * Return the Longest Command / Subcommand length
     */
    protected function getLongestCommandSubcommandLength(): int
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
     */
    public function getCommandDefinition($cmd): string
    {
        if (array_key_exists($cmd, static::COMMAND_DEFINITIONS) === false) {
            return '';
        }
        return static::COMMAND_DEFINITIONS[$cmd];
    }
}
