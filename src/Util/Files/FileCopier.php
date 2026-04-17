<?php namespace Pauldro\Minicli\v2\Util\Files;

use Pauldro\UtilityBelt\Files\FileCopier as FileCopierParent;

/**
 * Service for Copying files
 */
class FileCopier extends FileCopierParent{
    protected static $instance;

    public string $errorMsg;
    public string $lastCopiedFile;

    public static function instance() : FileCopier
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
