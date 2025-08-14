<?php namespace Pauldro\Minicli\v2\Output;
// Minicli
use Minicli\Output\ThemeStyle;
use Pauldro\Minicli\v2\Util\DataArray;

class ThemeConfig extends DataArray {
    public function __construct(array $list) {
        foreach ($list as $color => $theme) {
            $this->set($color, $theme);
        }
    }

    /**
     * Summary of make
     * @param  ThemeStyle[] $list
     * @return ThemeConfig
     */
    public static function make(array $list) : self 
    {
        return new self($list);
    }
}
