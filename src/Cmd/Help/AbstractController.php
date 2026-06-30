<?php namespace Pauldro\Minicli\v2\Cmd\Help;
// PHP Core
use ReflectionClass;
// Pauldro
use Pauldro\Minicli\v2\Cmd\AbstractController as ParentController;
use Pauldro\UtilityBelt\Strings;


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
    /**  @var array<string,string>*/
    const REQUIRED_ENV_VARS = [];

/* =============================================================
    Minicli Controller Contracts
============================================================= */
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

/* =============================================================
    Display Printing
============================================================= */
    /**
     * Display Command
     * @return void
     */
    protected function display() : void
    {
        $this->displayUsage();
        $this->displayDescription();
        $this->displayRequiredEnvVars();
        $this->displayOptions();
        $this->displayRequiredParams();
        $this->displayRequiredParamOrGroups();
        $this->displaySubcommands();
        $this->displayNotes();

        $this->printer->newline();
        $this->printer->newline();
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
            if (in_array($option, static::OPTIONS_SKIP)) {
                continue;
            }
            $printer->line(sprintf('%s%s%s', $printer->spaces(2), Strings::pad($example, $optLength), $this->getOptDefinition($option)));
        }
        return;
    }

    protected function displayRequiredEnvVars() : void
    {
        if (empty(static::REQUIRED_ENV_VARS)) {
            return;
        }

        $printer = $this->printer;
        $optLength = Strings::longestStrlen(array_keys(static::REQUIRED_ENV_VARS));
        $printer->info('Required .env variable:');

        foreach (static::REQUIRED_ENV_VARS as $var => $description) {
            $example = Strings::pad($var, $optLength);
            $printer->line(sprintf('%s%s%s', $printer->spaces(2), $example, $description));
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
     * Display Params that are required if others are not present
     * @return void
     */
    protected function displayRequiredParamOrGroups() : void
    {
        if (empty(static::REQUIRED_PARAM_OR_GROUPS)) {
            return;
        }

        $printer = $this->printer;
        $printer->line($printer->style('Required:', 'info_header'));

        foreach (static::REQUIRED_PARAM_OR_GROUPS as $key => $rule) {
            $printer->line($printer->style($printer->spaces(2) . $rule['description'], 'bold'));
            $i = 1;
            $indent = $printer->spaces(4);

            foreach ($rule['groups'] as $groupid  => $group) {
                foreach ($group as $option) {
                    $example = static::OPTIONS[$option];
                    $printer->line(sprintf('%s%s', $printer->spaces(4), $example));
                }
                if ($i < sizeof($rule['groups'])) {
                    $printer->info($indent . "*** OR ***");
                    $printer->newline();
                }
                $i++;
            }
        }
        return;
    }

    /**
     * Display Command Description
     * @return void
     */
    protected function displayDescription() : void
    {
        $printer = $this->printer;
        $printer->line($printer->style('Description:', 'info_header'));
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
     * @return void
     */
    protected function displaySubcommand() : void
    {
        if (in_array($this->input->lastArg(), static::SUBCOMMANDS) === false) {
            return;
        }
        $handler = $this->getSubcommandController($this->input->subcommand, $this->input->lastArg());
        if (empty($handler)) {
            return;
        }
        $handler->boot($this->app, $this->input);
        $handler->handle();
        return;
    }

/* =============================================================
    Supplemental
============================================================= */
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

    protected function getSubcommandController(string $command, string $subcommand) : ParentController|false {
        $reflector = new ReflectionClass(get_class($this));
        $ns = $reflector->getNamespaceName() . '\\' . ucfirst($command) . '\\';
        $class = $ns . ucfirst($subcommand) . 'Controller';

        if (class_exists($class) === false) {
            return $this->error("Controller not found: $class");
        }
        return new $class();
    }
}
