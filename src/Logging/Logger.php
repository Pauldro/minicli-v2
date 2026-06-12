<?php namespace Pauldro\Minicli\v2\Logging;
// Minicli
use Minicli\App;
use Minicli\ServiceInterface;
// Pauldro Minicli
use Pauldro\Minicli\v2\Cmd\CommandCall;

/**
 * Logger
 * Service that Logs Messages to files
 */
class Logger implements ServiceInterface {
    protected const DEFAULT_TIMESTAMP_FORMAT = 'Y-m-d H:i:s';
    protected const LOG_COLUMN_DELIMITER = "\t";

    protected string $logsPath;
    protected LogFileType $logFileType;
    protected string $timestampFormat;

    public function load(App $app) : void
    {
        $config = $app->config;

        $this->logsPath = $app->logs_path;
        $this->logFileType = LogFileType::from($config->logging['file_type'] ?? LogFileType::SINGLE->value);
        $this->timestampFormat = $config->logging['timestamp_format'] ?? self::DEFAULT_TIMESTAMP_FORMAT;
    }

/* =============================================================
    LogFilepath Functions
============================================================= */
    /**
     * Return Path to Log File
     * @param  LogFile $file
     * @param  string  $suffix
     * @return string
     */
    protected function getLogFilePath(LogFileInterface $file, string $suffix = '') : string
    {
        $filename = $file->value;

        if ($suffix) {
            return sprintf("{$this->logsPath}/$filename-%s.log", $suffix);
        }

        return match ($this->logFileType) {
            LogFileType::DAILY => sprintf("{$this->logsPath}/$filename-%s.log", date('Y-m-d')),
            default => "{$this->logsPath}/$filename.log",
        };
    }

/* =============================================================
    Logging Functions
============================================================= */
    /**
     * @param  string $message
     * @param  array<mixed> $context
     * @return void
     */
    public function command(string $message, array $context = []) : void
    {
        $this->log($message, $context, LogFile::Command);
    }

    /**
     * @param  string $message
     * @param  array<mixed> $context
     * @return void
     */
    public function info(string $message, array $context = []) : void
    {
        $this->log($message, $context, LogFile::Info);
    }

    /**
     * @param  string $message
     * @param  array<mixed> $context
     * @return void
     */
    public function warning(string $message, array $context = []) : void
    {
        $this->log($message, $context, LogFile::Warning);
    }

    /**
     * @param string $message
     * @param array<mixed> $context
     * @return void
     */
    public function error(string $message, array $context = []) : void
    {
        $this->log($message, $context, LogFile::Error);
    }

    /**
     * @param  string $message
     * @param  array<mixed> $context
     * @return void
     */
    public function debug(string $message, array $context = []) : void
    {
        $this->log($message, $context, LogFile::Debug);
    }

    /**
     * @param  string $message
     * @param  array<mixed> $context
     * @param  LogFileInterface|null $file
     * @return void
     */
    public function log(string $message, array $context = [], ?LogFileInterface $file = null) : void
    {
        $delimiter = static::LOG_COLUMN_DELIMITER;

        $this->addToLog(sprintf(
            "[%s]$delimiter%s$delimiter%s\n",
            date($this->timestampFormat),
            $message,
            [] === $context ? '' : json_encode($context)
        ), $file);
    }

    /**
     * Add Message to Log
     * @param  string $message
     * @param  LogFileInterface $file
     * @return void
     */
    protected function addToLog(string $message, LogFileInterface $file) : void
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
     * Clear Log
     * @param  LogFile $file
     * @return void
     */
    public function clearLog(LogFileInterface $file) : void
    {
        $logFile = $this->getLogFilePath($file);

        if (file_exists($logFile) === false) {
            return;
        }
        file_put_contents($logFile, '');
    }

    /**
     * Archive Log for the last month
     * @param  LogFileInterface $file
     * @return bool
     */
    public function archiveLogPrevMonth(LogFileInterface $file) : bool
    {
        $date = date('Ym', strtotime("-1 month"));

        $logFilepath = $this->getLogFilePath($file);

        $archiveFilepath = $this->getLogFilePath($file, $date);

        if (file_exists($logFilepath) === false) {
            file_put_contents($archiveFilepath, '');
            return false;
        }
        copy($logFilepath, $archiveFilepath);
        if (file_exists($archiveFilepath) === false) {
            echo $archiveFilepath , "does not exist " . PHP_EOL;
            return false;
        }
        file_put_contents($logFilepath, '');
        return true;
    }

/* =============================================================
    Log Reading Functions
============================================================= */
    /**
     * Return the Last Line of a LogFile
     * @param  LogFile $file
     * @return string
     */
    public function getLastLogLine(LogFileInterface $file) : string
    {
        $logFile = $this->getLogFilePath($file);

        if (file_exists($logFile) === false) {
            return '';
        }
        return $this->tailLog($logFile);
    }

    /**
     * Find the Last Line of a file
     * @param  string  $filepath
     * @param  bool    $adaptive
     * @return string
     */
    protected function tailLog(string $filepath, $adaptive = true) : string {
        $lines = 1;

        // Open file
        $f = @fopen($filepath, "rb");
        if ($f === false) return '';

        // Sets buffer size, according to the number of lines to retrieve.
        // This gives a performance boost when reading a few lines from the file.
        if (!$adaptive) $buffer = 4096;
        else $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));

        // Jump to last character
        fseek($f, -1, SEEK_END);

        // Read it and adjust line number if necessary
        // (Otherwise the result would be wrong if file doesn't end with a blank line)
        if (fread($f, 1) != "\n") $lines -= 1;
        
        // Start reading
        $output = '';
        $chunk = '';

        // While we would like more
        while (ftell($f) > 0 && $lines >= 0) {

            // Figure out how far back we should jump
            $seek = min(ftell($f), $buffer);

            // Do the jump (backwards, relative to where we are)
            fseek($f, -$seek, SEEK_CUR);

            // Read a chunk and prepend it to our output
            $output = ($chunk = fread($f, $seek)) . $output;

            // Jump back to where we started reading
            fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);

            // Decrease our line counter
            $lines -= substr_count($chunk, "\n");
        }

        // While we have too many lines
        // (Because of buffer size we might have read too many)
        while ($lines++ < 0) {
            // Find first newline and remove all text before that
            $output = substr($output, strpos($output, "\n") + 1);
        }

        // Close file and return
        fclose($f);
        return trim($output);
    }

/* =============================================================
    Log String Functions
============================================================= */
    /**
     * Return array formatted as string for Log delimited by \t
     * @param  array $parts
     * @return string
     */
    public static function createLogString($parts = []) : string
    {
        return implode("\t", $parts);
    }

    /**
     * Sanitize Command for Log Use
     * @return string
     */
    public static function sanitizeCmdForLog(CommandCall $input, array $sensitiveParams) : string
    {
        $cmd = implode(' ', $input->getRawArgs());

        foreach ($sensitiveParams as $param) {
            $find = "$param=" . $input->getParam($param);
            $cmd  = str_replace($find, "$param=***", $cmd);
        }
        return $cmd;
    }
}
