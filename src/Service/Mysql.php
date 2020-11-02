<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Service;

use RuntimeException;
use mysqli;
use mysqli_result;
use GyMadarasz\WebApp\Service\Config;

class Mysql
{
    private mysqli $mysqli;
    private bool $connected = false;

    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function connect(): void
    {
        if ($this->connected) {
            return;
        }
        $this->mysqli = new mysqli($this->config->get('mysqlHost'), $this->config->get('mysqlUser'), $this->config->get('mysqlPassword'), $this->config->get('mysqlDatabase'));
        if ($this->mysqli->connect_error) {
            throw new RuntimeException('MySQL connection error: (' . $this->mysqli->connect_errno . ')' . $this->mysqli->connect_error);
        }
        $this->connected = true;
    }

    public function escape(string $value): string
    {
        $this->connect();
        return $this->mysqli->escape_string($value);
    }

    /**
     * @return array<string>
     */
    public function selectOne(string $query): array
    {
        $this->connect();
        $result = $this->mysqli->query($query);
        if ($result instanceof mysqli_result) {
            return $result->fetch_assoc() ?: [];
        }
        throw new RuntimeException("MySQL query error:\n$query\nMessage: {$this->mysqli->error}");
    }

    /**
     * @return array<array<string>>
     */
    public function select(string $query): array
    {
        $this->connect();
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
        $this->connect();
        if ($ret = (bool)$this->mysqli->query($query)) {
            return $ret;
        }
        throw new RuntimeException("MySQL query error:\n$query\nMessage: {$this->mysqli->error}");
    }

    public function update(string $query): int
    {
        if (!$this->query($query)) {
            return 0;
        }
        return $this->mysqli->affected_rows;
    }

    public function insert(string $query): int
    {
        if (!$this->query($query)) {
            return 0;
        }
        return (int)$this->mysqli->insert_id;
    }
}
