<?php namespace Pauldro\Minicli\v2\Output;
// Minicli
use Minicli\Output\OutputHandler as MinicliOutputHandler;

/**
 * OutputHandler
 * Handles CLI output
 */
class OutputHandler extends MinicliOutputHandler {
    /**
	 * Print Spaces
	 * @param  int   $spaces
	 * @return string
	 */
	public function spaces($spaces = 0) {
		return str_pad('', $spaces , ' ');
	}

	/**
	 * Displays content using the "default" style
	 * @param string $content
	 * @param bool $alt Whether or not to use the inverted style ("alt")
	 * @return void
	 */
	public function line($content, $alt = false) {
		$this->out($content, $alt ? "alt" : "default");
		$this->newline();
	}
}
