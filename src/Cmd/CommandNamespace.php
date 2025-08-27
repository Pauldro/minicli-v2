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

    /**
     * get namespace
     *
     * @param string $filename
     * @return string
     */
    protected function getNamespace(string $filename): string
    {
        $file = (array) file($filename);
        $lines = (array) preg_grep('/^namespace /', $file);

        if (empty($lines)) { // Try loading lines that namespace is on the same line as php
            $found = (array) preg_grep('/^\<\?php namespace /', $file);
            $lines = [];
            foreach ($found as $key => $line) {
                $lines[$key] = ltrim($line, '<?php ');
            }
        }

        $namespaceLine = trim(array_shift($lines));
        $match = [];
        preg_match('/^namespace (.*);$/', $namespaceLine, $match);

        return (string) array_pop($match);
    }
}