<?php namespace Pauldro\Minicli\v2\Util\Files\Directory;
// Pauldro
use Pauldro\UtilityBelt\Files\Directory\FileFetcher as FileFetcherParent;

/**
 * Wrapper for fetching files from a single directory
 */
class FileFetcher extends FileFetcherParent {
    protected string $dir;
    protected $fetcher;
    public string $errorMsg;
}
