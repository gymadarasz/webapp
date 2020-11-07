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
use GyMadarasz\WebApp\Service\User;
use GyMadarasz\WebApp\Service\Globals;

/**
 * LoginPage
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Controller
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class LoginPage
{
    /**
     * Method viewLogin
     *
     * @param Template $template template
     *
     * @return Template
     */
    public function viewLogin(Template $template): Template
    {
        return $template->create(
            'index.html.php',
            [
            'body' => 'login.html.php',
            ]
        );
    }

    /**
     * Method doLogin
     *
     * @param Template $template template
     * @param User     $user     user
     * @param Globals  $globals  globals
     *
     * @return Template
     */
    public function doLogin(
        Template $template,
        User $user,
        Globals $globals
    ): Template {
        if ($user->doAuth(
            $globals->getPost('email', ''),
            $globals->getPost('password', '')
        )
        ) {
            $output = $template->create(
                'index.html.php',
                [
                'body' => 'main.html.php',
                ]
            );
            $output->set('message', 'Login success');
        } else {
            $output = $template->create(
                'index.html.php',
                [
                'body' => 'login.html.php',
                ]
            );
            $output->set('error', 'Login failed');
        }

        return $output;
    }
}
