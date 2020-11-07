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

/**
 * ErrorPage
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Controller
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class ErrorPage
{
    /**
     * Method viewError
     *
     * @param Config   $config   config
     * @param Template $template template
     *
     * @return Template
     */
    public function viewError(Config $config, Template $template): Template
    {
        $output = $template->create(
            'index.html.php',
            [
            'body' => 'error-page.html.php',
            ]
        );
        $output->set('error', 'Request is not supported.');
        $output->set('base', $config->get('baseUrl'));
        return $output;
    }
}
