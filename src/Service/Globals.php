<?php declare(strict_types = 1);

namespace Madsoft\App\Service;

class Globals
{
    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public function getGet(string $name, $default = null)
    {
        return isset($_GET[$name]) ? $_GET[$name] : $default;
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public function getPost(string $name, $default = null)
    {
        return isset($_POST[$name]) ? $_POST[$name] : $default;
    }

    public function sessionStart(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function sessionDestroy(): void
    {
        session_destroy();
    }

    /**
     * @param mixed $value
     */
    public function setSession(string $name, $value): void
    {
        $_SESSION[$name] = $value;
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public function getSession(string $name, $default = null)
    {
        return isset($_SESSION[$name]) ? $_SESSION[$name] : $default;
    }
}
