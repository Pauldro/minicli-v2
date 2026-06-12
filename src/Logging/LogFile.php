<?php namespace Pauldro\Minicli\v2\Logging;

enum LogFile : string implements LogFileInterface 
{
    case Command = 'command';
    case Debug = 'debug';
    case Error = 'error';
    case Info = 'info';
    case Warning = 'warning';
}
