<?php namespace Pauldro\Minicli\v2\Util\Files;

use Pauldro\UtilityBelt\Files\FileFetcher as FileFetcherParent;


/**
 * Utility for fetching file contents
 */
class FileFetcher extends FileFetcherParent {
    protected static $instance;
    public string $errorMsg;
}
