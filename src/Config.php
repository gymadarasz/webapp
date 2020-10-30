<?php

namespace Madsoft\App;

use RuntimeException;
use Madsoft\App\Config\DevConfig;
use Madsoft\App\Config\TestConfig;
use Madsoft\App\Config\LiveConfig;

class Config
{
    const CONFIG_PATH = __DIR__ . '/config';
    const ENV_FILE = __DIR__ . '/config/env.php';

    /** @var ?array<mixed> */
    private static ?array $data = null;

    /**
     * @return mixed
     */
    public static function get(string $name)
    {
        if (null === Config::$data) {
            include Config::getConfigFile();
            Config::$data = get_defined_vars();
        }
        return Config::$data[$name];
    }

    private static function getConfigFile(): string
    {
        return Config::CONFIG_PATH . '/config.' . Config::getEnv() . '.php';
    }

    public static function getEnv(): string
    {
        $env = 'live';
        if (!file_exists(Config::ENV_FILE)) {
            Config::setEnv($env);
        }
        include Config::ENV_FILE;
        return $env;
    }

    public static function setEnv(string $env): void
    {
        file_put_contents(Config::ENV_FILE, "<?php \$env='$env'; ?>");
    }
}
