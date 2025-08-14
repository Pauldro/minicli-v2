<?php

declare(strict_types=1);

namespace Pauldro\Minicli\v2\Output\Theme;
// Minicli
use Minicli\Output\CLIColors;
use Minicli\Output\Theme\DefaultTheme;
use Minicli\Output\ThemeStyle;
// Pauldro Minicli
use Pauldro\Minicli\v2\Output\ThemeConfig;

/**
 * Provides Colors for Theme
 */
class CliTheme extends DefaultTheme {
    /**
     * DefaultTheme constructor.
     */
    public function __construct()
    {
        $styles = array_merge($this->getDefaultColors(), $this->getThemeColors());

        $formatted = [];
        foreach ($styles as $name => $style) {
            $formatted[$name] = ThemeStyle::make(...$style);
        }

        $this->config = ThemeConfig::make(...$formatted);
    }

    /**
     * get the colors
     *
     * @return array<string, array<int, string>>
     */
    public function getThemeColors(): array
    {
        return [
            'default'     => [CLIColors::$FG_WHITE],
            'alt'         => [CLIColors::$FG_BLACK, CLIColors::$BG_WHITE],
            'error'       => [CLIColors::$FG_RED],
            'error_alt'   => [CLIColors::$FG_WHITE, CLIColors::$BG_RED],
            'success'     => [CLIColors::$FG_GREEN],
            'success_alt' => [CLIColors::$FG_WHITE, CLIColors::$BG_GREEN],
            'info'        => [CLIColors::$FG_CYAN],
            'info_alt'    => [CLIColors::$FG_WHITE, CLIColors::$BG_CYAN],
            'info_header' => [CLIColors::$FG_BLUE],
            'bold'        => [CliColors::$BOLD],
            'dim'         => [CliColors::$DIM],
            'italic'      => [CliColors::$ITALIC],
            'underline'   => [CliColors::$UNDERLINE],
            'invert'      => [CliColors::$INVERT]
        ];
    }
}
