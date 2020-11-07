<?php declare(strict_types = 1);

/**
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
 * FormToken
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Service
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class FormToken
{
    protected static int $token = 0;

    protected Globals $globals;

    /**
     * Method __construct
     *
     * @param \GyMadarasz\WebApp\Service\Globals $globals globals
     */
    public function __construct(Globals $globals)
    {
        $this->globals = $globals;
    }

    /**
     * Method get
     *
     * @return string
     */
    public function get(): string
    {
        if (!self::$token) {
            self::$token = rand(100000000, 999999999);
            $this->globals->setSession('token', self::$token);
        }
        return '<input type="hidden" name="token" value="' . self::$token . '">';
    }

    /**
     * Method check
     *
     * @return bool
     */
    public function check(): bool
    {
        return $this
            ->globals->getSession('token') == $this->globals->getPost('token');
    }
}
