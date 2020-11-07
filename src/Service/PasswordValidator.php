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
 * PasswordValidator
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Service
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class PasswordValidator
{
    /**
     * Method getPasswordError
     *
     * @param string $password password
     *
     * @return string
     */
    public function getPasswordError(string $password): string
    {
        $passwordErr = '';
        if (strlen($password) < 8) {
            $passwordErr = "Your Password Must Contain At Least 8 Characters!";
        } elseif (!preg_match("#[0-9]+#", $password)) {
            $passwordErr = "Your Password Must Contain At Least 1 Number!";
        } elseif (!preg_match("#[A-Z]+#", $password)) {
            $passwordErr = "Your Password Must Contain At Least 1 Capital Letter!";
        } elseif (!preg_match("#[a-z]+#", $password)) {
            $passwordErr = "Your Password Must Contain At Least 1 Lowercase Letter!";
        }
        return $passwordErr;
    }
}
