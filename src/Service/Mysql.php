<?php declare(strict_types = 1);

namespace Madsoft\App\Service;

use RuntimeException;
use mysqli;
use mysqli_result;
use Madsoft\App\Service\Config;

class Mysql
{
    private mysqli $mysqli;

    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function connect(): void
    {
        $this->mysqli = new mysqli($this->config->get('mysqlHost'), $this->config->get('mysqlUser'), $this->config->get('mysqlPassword'), $this->config->get('mysqlDatabase'));
        if ($this->mysqli->connect_error) {
            throw new RuntimeException('MySQL connection error: (' . $this->mysqli->connect_errno . ')' . $this->mysqli->connect_error);
        }
    }

    public function escape(string $value): string
    {
        return $this->mysqli->escape_string($value);
    }

    /**
     * @return ?array<mixed>
     */
    public function selectOne(string $query): ?array
    {
        $result = $this->mysqli->query($query);
        if ($result instanceof mysqli_result) {
            return $result->fetch_assoc();
        }
        throw new RuntimeException("MySQL query error:\n$query\nMessage: {$this->mysqli->error}");
    }

    /**
     * @return array<mixed>
     */
    public function select(string $query): array
    {
        $result = $this->mysqli->query($query);
        if ($result instanceof mysqli_result) {
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            return $rows;
        }
        throw new RuntimeException("MySQL query error:\n$query\nMessage: {$this->mysqli->error}");
    }

    public function query(string $query): bool
    {
        if ($ret = (bool)$this->mysqli->query($query)) {
            return $ret;
        }
        throw new RuntimeException("MySQL query error:\n$query\nMessage: {$this->mysqli->error}");
    }
}
