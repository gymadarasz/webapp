<?php

namespace Madsoft\App;

use RuntimeException;
use mysqli;
use mysqli_result;

class Mysql
{
    private mysqli $mysqli;

    public function connect(): void
    {
        $this->mysqli = new mysqli(Config::get('mysqlHost'), Config::get('mysqlUser'), Config::get('mysqlPassword'), Config::get('mysqlDatabase'));
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
