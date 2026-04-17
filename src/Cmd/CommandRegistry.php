<?php namespace Pauldro\Minicli\v2\Cmd;
// Minicli
use Minicli\Command\CommandRegistry as ParentCommandRegistry;
use Minicli\ControllerInterface;
// Pauldro
use Pauldro\UtilityBelt\Strings;

class CommandRegistry extends ParentCommandRegistry {
    /**
     * get callable controller
     *
     * @param string $command
     * @param string $subcommand
     * @return ControllerInterface|null
     */
    public function getCallableController(string $command, string $subcommand = "default"): ?ControllerInterface
    {
        $command = strtolower(Strings::camelCase($command));
		$subcommand = strtolower(Strings::camelCase($subcommand));
        $namespace = $this->getNamespace($command);

        return $namespace?->getController($subcommand);
    }

    /**
     * register namespace
     *
     * @param string $commandNamespace
     * @param string $commandSource
     * @return void
     */
    public function registerNamespace(string $commandNamespace, string $commandSource): void
    {
        $namespace = new CommandNamespace($commandNamespace);
        $namespace->loadControllers($commandSource);
        $this->namespaces[mb_strtolower($commandNamespace)] = $namespace;
    }
}