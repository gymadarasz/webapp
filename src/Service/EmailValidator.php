<?php declare(strict_types = 1);

/**
 * EmailValidator
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
 * EmailValidator
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Service
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class EmailValidator
{
    /**
     * Method isValidEmail
     *
     * @param string $email email
     *
     * @return string
     */
    public function isValidEmail(string $email): string
    {
        return (string)filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
