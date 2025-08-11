<?php namespace Pauldro\Minicli\v2\Services;
// Minicli
use Minicli\App;
use Minicli\ServiceInterface;

/**
 * VendorfilepathParser
 * Utility for getting path to vendor directory
 */
class VendorFilepathParser implements ServiceInterface {
    private static $instance;
    private $env_dir = '';

    public static function instance() : VendorFilepathParser
    {
		return self::$instance;
	}

    public function load(App $app): void
    {
        $this->env_dir = $app->base_path;
        self::$instance = $this;
    }

    /**
     * Parse vendor path
     * @param string $path
     */
    public function parse(string $path) {
        if (str_starts_with($path, '@')) {
            $path = str_replace('@', $this->env_dir.'/vendor/', $path);
        }
        return $path;
    }
}
