<?php namespace Pauldro\Minicli\v2\Util;


class StringUtilities {
	public static function pad(string $cmd, int $length, $padding = ' ') : string
    {
		return str_pad($cmd, $length, $padding);
	}
}