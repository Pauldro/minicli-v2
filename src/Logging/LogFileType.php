<?php namespace Pauldro\Minicli\v2\Logging;

enum LogFileType: string
{
    case SINGLE = 'single';
    case DAILY = 'daily';
}
