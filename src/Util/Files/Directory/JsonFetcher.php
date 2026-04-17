<?php namespace Pauldro\Minicli\v2\Util\Files\Directory;
// Pauldro 
use Pauldro\UtilityBelt\Files\Directory\JsonFetcher as JsonFetcherParent;

/**
 * Wrapper for fetching JSON files from a single directory
 */
class JsonFetcher extends JsonFetcherParent {
    protected string $dir;
    protected $fetcher;
    public string $errorMsg;
}
