<?php namespace Pauldro\Minicli\v2\Cmd;
use ReflectionClass;
// Minicli
use Minicli\Command\CommandNamespace as MinicliCommandNamespace;
use Minicli\ControllerInterface;

class CommandNamespace extends MinicliCommandNamespace {
    /**
     * load command map
     * 
     * NOTE: skips abstract classes
     *
     * @param string $controllerFile
     * @return void
     */
    protected function loadCommandMap(string $controllerFile): void
    {
        $filename = basename($controllerFile);

        $controllerClass = str_replace('.php', '', $filename);
        $commandName = mb_strtolower(str_replace('Controller', '', $controllerClass));
        $fullClassName = sprintf("%s\\%s", $this->getNamespace($controllerFile), $controllerClass);

        $reflect = new ReflectionClass($fullClassName);

        if ($reflect->isAbstract()) {
            return;
        }

        /** @var ControllerInterface $controller */
        $controller = new $fullClassName();
        $this->controllers[$commandName] = $controller;
    }
}