<?php namespace Pauldro\Minicli\v2\App;
// Base PHP
use Exception;
use ReflectionException;
use Throwable;
// Minicli
use Minicli\App as MinicliApp;
use Minicli\ControllerInterface;
use Minicli\Exception\BindingResolutionException;
use Minicli\Exception\CommandNotFoundException;
use Minicli\Exception\MissingParametersException;
use Minicli\Output\Helper\ThemeHelper;
// Pauldro Minicli
use Pauldro\Minicli\v2\Cmd\AbstractController;
use Pauldro\Minicli\v2\Cmd\CommandCall;
use Pauldro\Minicli\v2\Cmd\CommandRegistry;
use Pauldro\Minicli\v2\Output\OutputHandler;
use Pauldro\Minicli\v2\Logging\Logger;


/**
 * @property Logger        $log
 * @property OutputHandler $cliprinter
 */
class App extends MinicliApp {
    private const DEFAULT_SIGNATURE = './minicli help';

/* =============================================================
    Inits, Boots, Loads
============================================================= */
    protected function loadDefaultServices() : void
    {
        parent::loadDefaultServices();
        $this->addService('log', new Logger());
    }

    protected function loadServices() : void
    {
        try  {
            parent::loadServices();
        } catch (Exception $e) {
            $this->log->error($e->getMessage());
            $this->setTheme('\Default');
            $this->printer->error($e->getMessage());
            die;
        }
    }

    /**
     * @param array<string, mixed> $config
     * @param string $signature
     * @throws BindingResolutionException|ReflectionException
     */
    public function boot(array $config, string $signature) : void
    {
        $this->loadConfig($config, $signature);
        $this->loadServices();
        $this->addService('commandRegistry', new CommandRegistry($this->parseCommandsPaths()));
        $this->setTheme($this->config->theme);
        
        if ($this->config->has('php_ini') === false) {
            return;
        }
        $this->parseSetInis();
    }

    private function parseCommandsPaths() : array
    {
        $commandsPath = $this->config->app_path;
        if ( ! is_array($commandsPath)) {
            $commandsPath = [$commandsPath];
        }

        $commandSources = [];
        foreach ($commandsPath as $path) {
            if (str_starts_with($path, '@')) {
                $path = str_replace('@', $this->base_path.'/vendor/', $path).'/Command';
            }
            $commandSources[] = $path;
        }
        return $commandSources;
    }

    /**
     * Parse, Set Inis
     * @return void
     */
    private function parseSetInis() {
        if ($this->config->has('php_ini') === false) {
            return;
        }
        $conf = $this->config->php_ini;
        $dir = rtrim($conf['dir'], '/') . '/';
        $files = array_key_exists('files', $conf) ? $conf['files'] : [];

        foreach ($files as $file) {
            $settings = parse_ini_file($dir.$file);

            foreach ($settings as $option => $value) {
                ini_set($option, $value);
            }
        }
    }

/* =============================================================
    Setters
============================================================= */
    public function setTheme(string $theme) : void
    {
        parent::setTheme($theme);
        $output = new OutputHandler();

        $output->registerFilter(
            (new ThemeHelper($theme))
                ->getOutputFilter()
        );
        $this->addService('cliprinter', $output);
    }

/* =============================================================
    Execution
============================================================= */
    /**
     * @param array<int,string> $argv
     * @return void
     * @throws CommandNotFoundException|Throwable
     */
    public function runCommand(array $argv = []) : void
    {
        $input = new CommandCall($argv);

        if (count($input->args) < 2) {
            $this->printSignature();
            return;
        }

        $controller = $this->commandRegistry->getCallableController((string) $input->command, $input->subcommand);

        if (empty($controller)) {
            $cmd = $input->command;

            if (strtolower($input->subcommand) == 'default') {
                $cmd .= " $input->subcommand";
            }
            $this->error("Controller not found for $cmd");
            return;
        }

        if ($controller instanceof ControllerInterface) {
            try {
                $controller->boot($this, $input);
                $controller->run($input);
                $controller->teardown();
                return;
            } catch (MissingParametersException $e) {
                $cmd = $this->log::sanitizeCmdForLog($input, $controller::SENSITIVE_PARAMS);
                $this->logger->error($e->getMessage(), [$cmd]);
                
                if ($controller instanceof AbstractController) {
                    $this->log->error($this->log::createLogString([$cmd, '->', $e->getMessage()]));
                }
                $this->error($e->getMessage());
                return;
            }
        }
        $this->runSingle($input);
    }
}
