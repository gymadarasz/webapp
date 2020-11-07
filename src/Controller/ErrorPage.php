<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Controller;

use GyMadarasz\WebApp\Service\Config;
use GyMadarasz\WebApp\Service\Template;

class ErrorPage
{
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
