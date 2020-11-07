<?php declare(strict_types = 1);

/**
 * User
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

use function sleep;
use function password_verify;
use function password_hash;
use function urlencode;
use function base64_encode;
use function md5;
use function rand;
use RuntimeException;
use GyMadarasz\WebApp\Service\Config;
use GyMadarasz\WebApp\Service\Globals;
use GyMadarasz\WebApp\Service\Mysql;

/**
 * User
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Service
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class User
{
    protected int $uid;

    protected string $email;

    protected Config $config;
    protected Globals $globals;
    protected Mysql $mysql;

    /**
     * Method __construct
     *
     * @param Config  $config  config
     * @param Globals $globals globals
     * @param Mysql   $mysql   mysql
     */
    public function __construct(Config $config, Globals $globals, Mysql $mysql)
    {
        $this->config = $config;
        $this->globals = $globals;
        $this->mysql = $mysql;
    }

    
    /**
     * Method doAuth
     *
     * @param string $email    email
     * @param string $password password
     *
     * @return bool
     */
    public function doAuth(string $email, string $password): bool
    {
        sleep($this->config->get('authSleep'));
        $emailEscaped = $this->mysql->escape($email);
        $query = "SELECT id, email, password FROM user "
                . "WHERE email = '$emailEscaped' AND active = 1 LIMIT 1;";
        $user = $this->mysql->selectOne($query);
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }
        $this->uid = (int)$user['id'];
        $this->email = $user['email'];
        $this->globals->setSession('user', $this);
        return true;
    }

    /**
     * Method doAuthByToken
     *
     * @param string $token token
     *
     * @return bool
     */
    public function doAuthByToken(string $token): bool
    {
        sleep($this->config->get('authSleep'));
        $tokenEscaped = $this->mysql->escape($token);
        $query = "SELECT id, email, password FROM user "
                . "WHERE token = '$tokenEscaped' AND active = 1 LIMIT 1;";
        $user = $this->mysql->selectOne($query);
        if (!$user) {
            return false;
        }
        $this->uid = (int)$user['id'];
        $this->email = $user['email'];
        return true;
    }

    /**
     * Method doActivate
     *
     * @param string $token token
     *
     * @return int
     */
    public function doActivate(string $token): int
    {
        $tokenEscaped = $this->mysql->escape($token);
        return $this->mysql->update(
            "UPDATE user SET active = 1, token = '' "
                . "WHERE token = '$tokenEscaped' LIMIT 1;"
        );
    }

    /**
     * Method createUser
     *
     * @param string $email    email
     * @param string $password password
     *
     * @return string|null
     */
    public function createUser(string $email, string $password): ?string
    {
        $emailEscaped = $this->mysql->escape($email);
        $passwordEscaped = $this->encrypt($password);
        $tokenEscaped = $this->mysql->escape($this->generateToken());
        $query = "INSERT INTO user (email, password, token, active) "
                . "VALUES ('$emailEscaped', '$passwordEscaped', '$tokenEscaped', 0)";
        if ($this->mysql->query($query)) {
            return $tokenEscaped;
        }
        return null;
    }

    /**
     * Method createToken
     *
     * @param string $email email
     *
     * @return string|null
     */
    public function createToken(string $email): ?string
    {
        $emailEscaped = $this->mysql->escape($email);
        $tokenEscaped = $this->mysql->escape($this->generateToken());
        $query = "UPDATE user SET token = '$tokenEscaped' "
                . "WHERE email = '$emailEscaped' LIMIT 1;";
        if ($this->mysql->query($query)) {
            return $tokenEscaped;
        }
        return null;
    }

    /**
     * Method changePassword
     *
     * @param string $password password
     *
     * @return bool
     */
    public function changePassword(string $password): bool
    {
        $idEscaped = (int)$this->uid;
        $passwordEscaped = $this->encrypt($password);
        $query = "UPDATE user SET password = '$passwordEscaped', token = '' "
                . "WHERE id = $idEscaped LIMIT 1;";
        return $this->mysql->query($query);
    }

    /**
     * Method encrypt
     *
     * @param string $password password
     *
     * @return string
     */
    public function encrypt(string $password): string
    {
        return (string)password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Method generateToken
     *
     * @return string
     */
    protected function generateToken(): string
    {
        return urlencode(
            base64_encode($this->encrypt(md5((string)rand(1, 1000000))))
        );
    }
}
