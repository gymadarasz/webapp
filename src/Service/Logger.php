<?php declare(strict_types = 1);

/**
 * Logger
 *
 * PHP version 7.4
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Service
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */

namespace GyMadarasz\WebApp\Service;

use function file_put_contents;
use function date;
use function get_class;
use Exception;
use RuntimeException;
use GyMadarasz\WebApp\Service\Config;

/**
 * Logger
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Service
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class Logger
{
    const CH_ERROR = 'error';
    const CH_WARNING = 'warning';
    const CH_INFO = 'info';
    const CH_DEBUG = 'debug';
    const CH_TEST = 'test';
    const CH_FAIL = 'fail';

    protected Config $config;

    /**
     * Variable channels
     *
     * @var string[] $channels
     */
    protected array $channels = [];

    /**
     * Method __construct
     *
     * @param Config $config config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Method setChannels
     *
     * @param string[] $channels channels
     *
     * @return void
     */
    public function setChannels(array $channels): void
    {
        $this->channels = $channels;
    }

    /**
     * Method doLogException
     *
     * @param Exception $e e
     *
     * @return void
     */
    public function doLogException(Exception $e): void
    {
        $this->error(
            'Exception occured: ' . get_class($e) . ': "' . $e->getMessage() .
                "\"\nTrace:\n" . $e->getTraceAsString()
        );
    }

    /**
     * Method error
     *
     * @param string $msg msg
     *
     * @return void
     */
    public function error(string $msg): void
    {
        $this->doLog(Logger::CH_ERROR, $msg);
    }

    /**
     * Method warning
     *
     * @param string $msg msg
     *
     * @return void
     */
    public function warning(string $msg): void
    {
        $this->doLog(Logger::CH_WARNING, $msg);
    }

    /**
     * Method info
     *
     * @param string $msg msg
     *
     * @return void
     */
    public function info(string $msg): void
    {
        $this->doLog(Logger::CH_INFO, $msg);
    }

    /**
     * Method debug
     *
     * @param string $msg msg
     *
     * @return void
     */
    public function debug(string $msg): void
    {
        $this->doLog(Logger::CH_DEBUG, $msg);
    }

    /**
     * Method test
     *
     * @param string $msg msg
     *
     * @return void
     */
    public function test(string $msg): void
    {
        $this->doLog(Logger::CH_TEST, $msg);
    }

    /**
     * Method fail
     *
     * @param string $msg msg
     *
     * @return void
     */
    public function fail(string $msg): void
    {
        $this->doLog(Logger::CH_FAIL, $msg);
    }
    
    /**
     * Method doLog
     *
     * @param string $channel channel
     * @param string $msg     msg
     *
     * @return void
     * @throws RuntimeException
     */
    protected function doLog(string $channel, string $msg): void
    {
        if (!$this->channels || in_array($channel, $this->channels)) {
            $fullmsg = "[" . date("Y-m-d H:i:s") . "] [$channel] $msg";
            if (!file_put_contents(
                $this->config->get('logFile'),
                "$fullmsg\n",
                FILE_APPEND
            )
            ) {
                throw new RuntimeException(
                    "Log file error, (" . $this->config->get('logFile') .
                        ") message is not logged: $fullmsg"
                );
            }
        }
    }
}
