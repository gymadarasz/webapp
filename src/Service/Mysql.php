<?php declare(strict_types = 1);

/**
 * Mysql
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

use RuntimeException;
use mysqli;
use mysqli_result;
use GyMadarasz\WebApp\Service\Config;

/**
 * Mysql
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Service
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class Mysql
{
    protected mysqli $mysqli;
    protected bool $connected = false;

    protected Config $config;

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
     * Method connect
     *
     * @return void
     * @throws RuntimeException
     */
    public function connect(): void
    {
        if ($this->connected) {
            return;
        }
        $this->mysqli = new mysqli(
            $this->config->get('mysqlHost'),
            $this->config->get('mysqlUser'),
            $this->config->get('mysqlPassword'),
            $this->config->get('mysqlDatabase')
        );
        if ($this->mysqli->connect_error) {
            throw new RuntimeException(
                'MySQL connection error: (' . $this->mysqli->connect_errno . ')' .
                    $this->mysqli->connect_error
            );
        }
        $this->connected = true;
    }

    /**
     * Method escape
     *
     * @param string $value value
     *
     * @return string
     */
    public function escape(string $value): string
    {
        $this->connect();
        return $this->mysqli->escape_string($value);
    }

    /**
     * Method selectOne
     *
     * @param string $query query
     *
     * @return string[]
     * @throws RuntimeException
     */
    public function selectOne(string $query): array
    {
        $this->connect();
        $result = $this->mysqli->query($query);
        if ($result instanceof mysqli_result) {
            return $result->fetch_assoc() ?: [];
        }
        throw new RuntimeException(
            "MySQL query error:\n$query\nMessage: {$this->mysqli->error}"
        );
    }

    /**
     * Method select
     *
     * @param string $query query
     *
     * @return string[][]
     * @throws RuntimeException
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
        throw new RuntimeException(
            "MySQL query error:\n$query\nMessage: {$this->mysqli->error}"
        );
    }

    /**
     * Method query
     *
     * @param string $query query
     *
     * @return bool
     * @throws RuntimeException
     */
    public function query(string $query): bool
    {
        $this->connect();
        $ret = (bool)$this->mysqli->query($query);
        if ($ret) {
            return $ret;
        }
        throw new RuntimeException(
            "MySQL query error:\n$query\nMessage: {$this->mysqli->error}"
        );
    }

    /**
     * Method update
     *
     * @param string $query query
     *
     * @return int
     */
    public function update(string $query): int
    {
        if (!$this->query($query)) {
            return 0;
        }
        return $this->mysqli->affected_rows;
    }

    /**
     * Method insert
     *
     * @param string $query query
     *
     * @return int
     */
    public function insert(string $query): int
    {
        if (!$this->query($query)) {
            return 0;
        }
        return (int)$this->mysqli->insert_id;
    }
}
