<?php namespace Pauldro\Minicli\v2\App;
// Base PHP
// use ReflectionException;
use Throwable;
// Minicli
use Minicli\App as MinicliApp;
use Minicli\ControllerInterface;
use Minicli\Exception\CommandNotFoundException;
use Minicli\Exception\MissingParametersException;
use Minicli\Output\Helper\ThemeHelper;
// Pauldro Minicli
use Pauldro\Minicli\v2\Cmd\AbstractController;
use Pauldro\Minicli\v2\Cmd\CommandCall;
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
