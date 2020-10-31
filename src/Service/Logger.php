<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Service;

use function file_put_contents;
use function date;
use function get_class;
use Exception;
use RuntimeException;
use GyMadarasz\WebApp\Service\Config;

class Logger
{
    const CH_ERROR = 'error';
    const CH_WARNING = 'warning';
    const CH_INFO = 'info';
    const CH_DEBUG = 'debug';
    const CH_TEST = 'test';
    const CH_FAIL = 'fail';

    private Config $config;

    /** @var array<string> $channels */
    private array $channels = [];

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /** @param array<string> $channels */
    public function setChannels(array $channels): void
    {
        $this->channels = $channels;
    }

    public function doLogException(Exception $e): void
    {
        $this->error('Exception occured: ' . get_class($e) . ': "' . $e->getMessage() . "\"\nTrace:\n" . $e->getTraceAsString());
    }

    public function error(string $msg): void
    {
        $this->doLog(Logger::CH_ERROR, $msg);
    }

    public function warning(string $msg): void
    {
        $this->doLog(Logger::CH_WARNING, $msg);
    }

    public function info(string $msg): void
    {
        $this->doLog(Logger::CH_INFO, $msg);
    }

    public function debug(string $msg): void
    {
        $this->doLog(Logger::CH_DEBUG, $msg);
    }

    public function test(string $msg): void
    {
        $this->doLog(Logger::CH_TEST, $msg);
    }

    public function fail(string $msg): void
    {
        $this->doLog(Logger::CH_FAIL, $msg);
    }
    
    protected function doLog(string $channel, string $msg): void
    {
        if (!$this->channels || in_array($channel, $this->channels)) {
            $fullmsg = "[" . date("Y-m-d H:i:s") . "] [$channel] $msg";
            if (!file_put_contents($this->config->get('logFile'), "$fullmsg\n", FILE_APPEND)) {
                throw new RuntimeException("Log file error, (" . $this->config->get('logFile') . ") message is not logged: $fullmsg");
            }
        }
    }
}
