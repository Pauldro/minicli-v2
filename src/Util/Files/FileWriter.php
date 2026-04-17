<?php namespace Pauldro\Minicli\v2\Util\Files;

use Pauldro\UtilityBelt\Files\FileWriter as FileWriterParent;

/**
 * Writes Files
 *
 * @property string $errorMsg
 * @property string $lastWrittenFile
 */
class FileWriter extends FileWriterParent {
    private static $instance;
    public $errorMsg;
    public $lastWrittenFile;

    public static function instance() : FileWriter
    {
        if (empty(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }
}
