<?php declare(strict_types = 1);

/**
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

use RuntimeException;
use GyMadarasz\WebApp\Service\Config\DevConfig;
use GyMadarasz\WebApp\Service\Config\TestConfig;
use GyMadarasz\WebApp\Service\Config\LiveConfig;

/**
 * Config
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Service
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class Config
{
    const CONFIG_PATH = __DIR__ . '/../config';
    const ENV_FILE = __DIR__ . '/../config/env.php';

    /**
     * Variable data
     *
     * @var ?mixed[] data
     */
    protected static ?array $data = null;

    protected static string $extPath = Config::CONFIG_PATH;

    /**
     * Method setExtPath
     *
     * @param string $extPath extPath
     *
     * @return void
     */
    public function setExtPath(string $extPath): void
    {
        Config::$extPath = $extPath;
    }

    /**
     * Method get
     *
     * @param string $name name
     *
     * @return mixed
     */
    public function get(string $name)
    {
        if (null === Config::$data) {
            include $this->getConfigFile();
            include $this->getConfigExtFile();
            Config::$data = get_defined_vars();
        }
        return Config::$data[$name];
    }

    /**
     * Method getConfigFile
     *
     * @return string
     */
    protected function getConfigFile(): string
    {
        return Config::CONFIG_PATH . '/config.' . $this->getEnv() . '.php';
    }

    /**
     * Method getConfigExtFile
     *
     * @return string
     */
    protected function getConfigExtFile(): string
    {
        return Config::$extPath . '/config.' . $this->getEnv() . '.php';
    }

    /**
     * Method getEnv
     *
     * @return string
     */
    public function getEnv(): string
    {
        $env = 'live';
        if (!file_exists(Config::ENV_FILE)) {
            $this->setEnv($env);
        }
        include Config::ENV_FILE;
        return $env;
    }

    /**
     * Method setEnv
     *
     * @param string $env env
     *
     * @return void
     */
    public function setEnv(string $env): void
    {
        file_put_contents(Config::ENV_FILE, "<?php \$env = '$env';");
    }
}
