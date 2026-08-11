<?php
namespace Pauldro\Minicli\v2\Output;

// Minicli
use Minicli\Output\OutputHandler as MinicliOutputHandler;

/**
 * OutputHandler
 * Handles CLI output
 */
class OutputHandler extends MinicliOutputHandler
{
    /**
     * Return spaces
     * @param  int   $spaces
     */
    public function spaces($spaces = 0): string
    {
        return str_pad('', $spaces, ' ');
    }

    /**
     * Displays content using the "default" style
     * @param  string $content
     * @param  bool $alt Whether or not to use the inverted style ("alt")
     */
    public function line($content, $alt = false): void
    {
        $this->out($content, $alt ? 'alt' : 'default');
        $this->newline();
    }

    /**
     * Style Content
     * @param string $content
     * @param string $style
     */
    public function style($content, $style): string
    {
        return $this->printerAdapter->out($this->filterOutput($content, $style));
    }
}
