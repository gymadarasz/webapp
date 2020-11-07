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

use GyMadarasz\WebApp\Service\Config;
use GyMadarasz\WebApp\Service\Template;
use GyMadarasz\WebApp\Service\User;
use GyMadarasz\WebApp\Service\Globals;

/**
 * ActivatePage
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Controller
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class ActivatePage
{
    /**
     * Method viewActivate
     *
     * @param Config   $config   config
     * @param Template $template template
     * @param User     $user     user
     * @param Globals  $globals  globals
     *
     * @return Template
     */
    public function viewActivate(
        Config $config,
        Template $template,
        User $user,
        Globals $globals
    ): Template {
        if ($user->doActivate($globals->getGet('token'))) {
            $output = $template->create(
                'index.html.php',
                [
                'body' => 'login.html.php',
                ]
            );
            $output->set('message', 'Your account is now activated.');
        } else {
            $output = $template->create(
                'index.html.php',
                [
                'body' => 'error-page.html.php',
                ]
            );
            $output->set('error', 'Activation token is incorrect.');
            $output->set('base', $config->get('baseUrl'));
        }

        return $output;
    }
}
