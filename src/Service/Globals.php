<?php declare(strict_types = 1);

/**
 * Globals
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

/**
 * Globals
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Service
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 *
 * @SuppressWarnings(PHPMD.Superglobals)
 */
class Globals
{
    /**
     * Method getMethod
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Method getGet
     *
     * @param string $name    name
     * @param mixed  $default default
     *
     * @return mixed
     */
    public function getGet(string $name = null, $default = null)
    {
        if (null === $name) {
            return $_GET;
        }
        return isset($_GET[$name]) ? $_GET[$name] : $default;
    }

    /**
     * Method getPost
     *
     * @param string $name    name
     * @param mixed  $default default
     *
     * @return mixed
     */
    public function getPost(string $name = null, $default = null)
    {
        if (null === $name) {
            return $_POST;
        }
        return isset($_POST[$name]) ? $_POST[$name] : $default;
    }

    /**
     * Method sessionStart
     *
     * @return void
     */
    public function sessionStart(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Method sessionDestroy
     *
     * @return void
     */
    public function sessionDestroy(): void
    {
        session_destroy();
    }

    /**
     * Method setSession
     *
     * @param string $name  name
     * @param mixed  $value value
     *
     * @return void
     */
    public function setSession(string $name, $value): void
    {
        $_SESSION[$name] = $value;
    }

    /**
     * Method getSession
     *
     * @param string $name    name
     * @param mixed  $default default
     *
     * @return mixed
     */
    public function getSession(string $name, $default = null)
    {
        return isset($_SESSION[$name]) ? $_SESSION[$name] : $default;
    }
}
