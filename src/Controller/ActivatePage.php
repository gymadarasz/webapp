<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Controller;

use GyMadarasz\WebApp\Service\Config;
use GyMadarasz\WebApp\Service\Template;
use GyMadarasz\WebApp\Service\User;
use GyMadarasz\WebApp\Service\Globals;

class ActivatePage
{
    public function viewActivate(Config $config, Template $template, User $user, Globals $globals): Template
    {
        if ($user->doActivate($globals->getGet('token'))) {
            $output = $template->create('index.html.php', [
                'body' => 'login.html.php',
            ]);
            $output->set('message', 'Your account is now activated.');
        } else {
            $output = $template->create('index.html.php', [
                'body' => 'error-page.html.php',
            ]);
            $output->set('error', 'Activation token is incorrect.');
            $output->set('base', $config->get('baseUrl'));
        }

        return $output;
    }
}
