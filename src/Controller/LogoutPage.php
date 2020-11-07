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
use GyMadarasz\WebApp\Service\Globals;

/**
 * LogoutPage
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Controller
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class LogoutPage
{
    /**
     * Method viewLogout
     *
     * @param Template $template template
     * @param Globals  $globals  globals
     *
     * @return Template
     */
    public function viewLogout(Template $template, Globals $globals): Template
    {
        $globals->sessionDestroy();
        $output = $template->create(
            'index.html',
            [
            'body' => 'login.html',
            ]
        );
        $output->set('message', 'Logout success');

        return $output;
    }
}
