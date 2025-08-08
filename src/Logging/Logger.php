<?php namespace Pauldro\Minicli\v2\Logging;
// Minicli
use Minicli\App;
use Minicli\ServiceInterface;

/**
 * Logger
 * Service that Logs Messages to files
 */
class Logger implements ServiceInterface {
    private const DEFAULT_TIMESTAMP_FORMAT = 'Y-m-d H:i:s';

    private string $logsPath;

    private LogFileType $logFileType;
    private string $timestampFormat;

    public function load(App $app): void
    {
        $config = $app->config;

        $this->logsPath = $app->logs_path;
        $this->logFileType = LogFileType::from($config->logging['file_type'] ?? LogFileType::SINGLE->value);
        $this->timestampFormat = $config->logging['timestamp_format'] ?? self::DEFAULT_TIMESTAMP_FORMAT;
    }

    /**
     * @param  string $message
     * @param  array<mixed> $context
     * @param  LogFile|null $file
     * @return void
     */
    public function log(string $message, array $context = [], LogFile $file = null) : void
    {

       return  $this->writeLog(sprintf(
            "[%s] %s%s\n",
            date($this->timestampFormat),
            $message,
            [] === $context ? '' : ' - '.json_encode($context)
        ), $file);
    }

    /**
     * @param  string $message
     * @param  array<mixed> $context
     * @return void
     */
    public function info(string $message, array $context = []) : void
    {
        $this->log($message, $context, LogFile::INFO);
    }

    /**
     * @param  string $message
     * @param  array<mixed> $context
     * @return void
     */
    public function warning(string $message, array $context = []) : void
    {
        $this->log($message, $context, LogFile::WARNING);
    }

    /**
     * @param string $message
     * @param array<mixed> $context
     * @return void
     */
    public function error(string $message, array $context = []) : void
    {
        $this->log($message, $context, LogFile::ERROR);
    }

    /**
     * @param  string $message
     * @param  array<mixed> $context
     * @return void
     */
    public function debug(string $message, array $context = []) : void
    {
        $this->log($message, $context, LogFile::DEBUG);
    }

    /**
     * Add Message to Log
     * @param  string $message
     * @param  LogFile $file
     * @return void
     */
    private function writeLog(string $message, LogFile $file) : void
    {
        if (is_dir($this->logsPath) === false) {
            mkdir($this->logsPath, 0775, true);
        }

        $logFile = $this->getLogFilePath($file);

        if (file_exists($logFile) === false) {
            touch($logFile);
        }
        file_put_contents($logFile, $message, FILE_APPEND);
    }

    /**
     * Return Path to Log File
     * @param  LogFile $file
     * @return string
     */
    private function getLogFilePath(LogFile $file): string
    {
        $filename = $file->value;

        return match ($this->logFileType) {
            LogFileType::DAILY => sprintf("{$this->logsPath}/$filename-%s.log", date('Y-m-d')),
            default => "{$this->logsPath}/$filename.log",
        };
    }

    /**
	 * Return array formatted as string for Log delimited by \t
	 * @param  array $parts
	 * @return string
	 */
	public function createLogString($parts = []) {
		return implode("\t", $parts);
	}
}
