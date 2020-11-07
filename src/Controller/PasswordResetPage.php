<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Controller
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */

namespace GyMadarasz\WebApp\Controller;

use GyMadarasz\WebApp\Service\Template;

/**
 * PasswordResetPage
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Controller
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class PasswordResetPage
{
    /**
     * Method viewPasswordReset
     *
     * @param Template $template template
     *
     * @return Template
     */
    public function viewPasswordReset(Template $template): Template
    {
        return $template->create(
            'index.html.php',
            [
            'body' => 'pwdreset.html.php',
            ]
        );
    }
}
