<?php namespace Pauldro\Minicli\v2\Logging;

enum LogFile : string implements LogFileInterface 
{
    case INFO = 'info';
    case WARNING = 'warning';
    case ERROR = 'error';
    case DEBUG = 'debug';
}
