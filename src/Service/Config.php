<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Service;

use RuntimeException;
use GyMadarasz\WebApp\Service\Config\DevConfig;
use GyMadarasz\WebApp\Service\Config\TestConfig;
use GyMadarasz\WebApp\Service\Config\LiveConfig;

class Config
{
    const CONFIG_PATH = __DIR__ . '/../config';
    const ENV_FILE = __DIR__ . '/../config/env.php';

    /** @var ?array<mixed> */
    private static ?array $data = null;

    /**
     * @return mixed
     */
    public function get(string $name)
    {
        if (null === Config::$data) {
            include $this->getConfigFile();
            Config::$data = get_defined_vars();
        }
        return Config::$data[$name];
    }

    private function getConfigFile(): string
    {
        return Config::CONFIG_PATH . '/config.' . $this->getEnv() . '.php';
    }

    public function getEnv(): string
    {
        $env = 'live';
        if (!file_exists(Config::ENV_FILE)) {
            $this->setEnv($env);
        }
        include Config::ENV_FILE;
        return $env;
    }

    public function setEnv(string $env): void
    {
        file_put_contents(Config::ENV_FILE, "<?php \$env = '$env';");
    }
}
