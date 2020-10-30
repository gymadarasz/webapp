<?php

namespace Madsoft\App;

use function sleep;
use function password_verify;
use function password_hash;
use function urlencode;
use function base64_encode;
use function md5;
use function rand;
use RuntimeException;
use Madsoft\App\Globals;
use Madsoft\App\Mysql;

final class User
{
    private int $id;

    private string $email;

    private Globals $globals;
    private Mysql $mysql;

    public function __construct(Globals $globals, Mysql $mysql)
    {
        $this->globals = $globals;
        $this->mysql = $mysql;
    }

    public function doAuth(string $email, string $password): bool
    {
        sleep(Config::get('authSleep'));
        $_email = $this->mysql->escape($email);
        $query = "SELECT id, email, password FROM user WHERE email = '$_email' AND active = 1 LIMIT 1;";
        $user = $this->mysql->selectOne($query);
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }
        $this->id = $user['id'];
        $this->email = $user['email'];
        $this->globals->setSession('user', $this);
        return true;
    }

    public function doAuthByToken(string $token): bool
    {
        sleep(Config::get('authSleep'));
        $_token = $this->mysql->escape($token);
        $query = "SELECT id, email, password FROM user WHERE token = '$_token' AND active = 1 LIMIT 1;";
        $user = $this->mysql->selectOne($query);
        if (!$user) {
            return false;
        }
        $this->id = $user['id'];
        $this->email = $user['email'];
        return true;
    }

    public function doActivate(string $token): bool
    {
        $_token = $this->mysql->escape($token);
        return $this->mysql->query("UPDATE user SET active = 1, token = '' WHERE token = '$_token' LIMIT 1;");
    }

    public function createUser(string $email, string $password): ?string
    {
        $_email = $this->mysql->escape($email);
        $_password = $this->encrypt($password);
        $_token = $this->mysql->escape($this->generateToken());
        $query = "INSERT INTO user (email, password, token, active) VALUES ('$_email', '$_password', '$_token', 0)";
        if ($this->mysql->query($query)) {
            return $_token;
        }
        return null;
    }

    public function createToken(string $email): ?string
    {
        $_email = $this->mysql->escape($email);
        $_token = $this->mysql->escape($this->generateToken());
        $query = "UPDATE user SET token = '$_token' WHERE email = '$_email' LIMIT 1;";
        if ($this->mysql->query($query)) {
            return $_token;
        }
        return null;
    }

    public function changePassword(string $password): bool
    {
        $_id = (int)$this->id;
        $_password = $this->encrypt($password);
        $query = "UPDATE user SET password = '$_password', token = '' WHERE id = $_id LIMIT 1;";
        return $this->mysql->query($query);
    }

    public function encrypt(string $password): string
    {
        return (string)password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    private function generateToken(): string
    {
        return urlencode(base64_encode($this->encrypt(md5((string)rand(1, 1000000)))));
    }
}
