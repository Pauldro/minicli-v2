<?php namespace Pauldro\Minicli\v2\Util\Files\Directory;
// Pauldro Minicli
use Pauldro\Minicli\v2\Util\Files\FileFetcher as Fetcher;

/**
 * JsonFetcher
 * Wrapper for fetching JSON files from a single directory
 */
class JsonFetcher extends FileFetcher {
    protected string $dir;
    protected Fetcher $fetcher;
    protected string $errorMsg;

    /**
	 * Return Filepath
	 * @param  string $filename
	 * @return string
	 */
	public function filepath(string $filename) : string
    {
        $filename = rtrim($filename, '.json') . '.json';
		return parent::filepath($filename);
	}
}
