<?php namespace Pauldro\Minicli\v2\Util\Files\Directory;
// Pauldro Minicli
use Pauldro\Minicli\v2\Util\Files\JsonFetcher as Fetcher;

/**
 * JsonFetcher
 * Wrapper for fetching JSON files from a single directory
 */
class JsonFetcher extends FileFetcher {
    protected string $dir;
    protected Fetcher $fetcher;
    protected string $errorMsg;

	public function __construct(string $dir) {
		parent::__construct($dir);
		$this->fetcher = Fetcher::instance();
	}

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
