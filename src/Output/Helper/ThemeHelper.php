<?php

declare(strict_types=1);

namespace Pauldro\Minicli\v2\Output\Helper;
// Minicli
use Minicli\Output\Helper\ThemeHelper as MinicliThemeHelper;

class ThemeHelper extends MinicliThemeHelper {
    /**
     * Parses the theme config setting and returns a namespaced class name.
     *
     * @param  string $themeConfig
     * @return string
     */
    protected function parseThemeSetting(string $themeConfig): string
    {
        if (empty($themeConfig)) {
            return '';
        }

        if ('~' === $themeConfig[0]) {
            return '\Minicli\Output\Theme'.$themeConfig.'Theme';  // Built-in theme.
        }

        return $themeConfig.'Theme'; // User-defined theme.
    }
}