<?php namespace Pauldro\Minicli\v2\Util;
//
use Pauldro\UtilityBelt\Data\Data as DataParent;

/**
 * Container for Data
 *
 * @property array $data          Array where properties are stored
 * @property bool  $trackChanges  Track Changes?
 * @property array $changes       Array of previous values keyed by fieldnames
 */
class Data extends DataParent {
    protected $data = [];
    protected $trackChanges = false;
    protected $changes = [];
}
