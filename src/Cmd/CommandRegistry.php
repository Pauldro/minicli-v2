<?php namespace Pauldro\Minicli\v2\Cmd;
// Minicli
use Minicli\Command\CommandRegistry as ParentCommandRegistry;

class CommandRegistry extends ParentCommandRegistry {
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