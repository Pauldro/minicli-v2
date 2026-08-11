<?php namespace Pauldro\Minicli\v2\Cmd;
// Minicli
use Minicli\Command\CommandCall as ParentCommandCall;

/**
 * CommandCall
 * Parses Command Arguments ($argv) into segments
 * 1. Command
 * 2. Subcommand ** Optional **
 * 3. Parameters
 * 4. Flags (both --[] and -[])
 */
class CommandCall extends ParentCommandCall {

    /* =============================================================
        Public
    ============================================================= */
    /**
     * Return Last Argument sent
     */
    public function lastArg(): string
    {
        $i = (count($this->args) - 1);
        return array_key_exists($i, $this->args) ? $this->args[$i] : '';
    }

    /**
     * Return boolean value for parameter
     * @param  string $param Parameter to get Value from
     */
    public function getParamBool(string $param): bool
    {
        $value = $this->getParam($param);
        if (in_array($value, [null, '', '0'], true)) {
            return false;
        }
        return strtolower($value) === 'y' || strtolower($value) === 'true';
    }

    /**
     * Return Parameter Value as array
     * @param  string $param      Parameter Key
     * @param  string $delimiter  Delimiter
     */
    public function getParamArray(string $param, string $delimiter = ","): array
    {
        return explode($delimiter, $this->getParam($param));
    }

    /**
     * Return Param value as integer
     */
    public function getParamInt(string $param): int
    {
        return intval($this->getParam($param));
    }

    public function getParamString(string $param): string
    {
        return (string)$this->getParam($param);
    }

    /* =============================================================
        Internal
    ============================================================= */
    /**
     * Parse Command Input
     * @param  array $argv Input
     */
    #[\Override]
    protected function parseCommand(array $argv): void
    {
        foreach ($argv as $arg) {
            $pair = explode('=', $arg, 2);

            if (count($pair) === 2) {
                $this->params[$pair[0]] = $pair[1];
                continue;
            }

            if (str_starts_with($arg, '--')) {
                $this->flags[] = $arg;
                continue;
            }

            if (str_starts_with($arg, '-')) {
                $this->flags[] = $arg;
                continue;
            }

            $this->args[] = $arg;
        }
    }
}
