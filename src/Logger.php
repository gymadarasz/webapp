<?php

namespace Madsoft\App;

use function file_put_contents;
use function date;
use function get_class;
use Exception;
use RuntimeException;

class Logger
{
    const LVL_ERROR = 'error';
    const LVL_WARNING = 'warning';
    const LVL_INFO = 'info';
    const LVL_DEBUG = 'debug';

    public function doLogException(Exception $e): void
    {
        $this->error('Exception occured: ' . get_class($e) . ': "' . $e->getMessage() . "\"\nTrace:\n" . $e->getTraceAsString());
    }

    public function error(string $msg): void
    {
        $this->doLog(Logger::LVL_ERROR, $msg);
    }

    public function warning(string $msg): void
    {
        $this->doLog(Logger::LVL_WARNING, $msg);
    }

    public function info(string $msg): void
    {
        $this->doLog(Logger::LVL_INFO, $msg);
    }

    public function debug(string $msg): void
    {
        $this->doLog(Logger::LVL_DEBUG, $msg);
    }
    
    protected function doLog(string $level, string $msg): void
    {
        $fullmsg = "[" . date("Y-m-d H:i:s") . "] [$level] $msg";
        if (!file_put_contents(Config::get('logFile'), "$fullmsg\n", FILE_APPEND)) {
            throw new RuntimeException("Log file error, (" . Config::get('logFile') . ") message is not logged: $fullmsg");
        }
    }
}
