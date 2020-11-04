<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Controller;

use GyMadarasz\WebApp\Service\Template;
use GyMadarasz\WebApp\Service\User;
use GyMadarasz\WebApp\Service\Globals;

class LoginPage
{
    public function viewLogin(Template $template): Template
    {
        return $template->create('index.html.php', [
            'body' => 'login.html.php',
        ]);
    }

    public function doLogin(Template $template, User $user, Globals $globals): Template
    {
        if ($user->doAuth(
            $globals->getPost('email', ''),
            $globals->getPost('password', '')
        )) {
            $output = $template->create('index.html.php', [
                'body' => 'main.html.php',
            ]);
            $output->set('message', 'Login success');
        } else {
            $output = $template->create('index.html.php', [
                'body' => 'login.html.php',
            ]);
            $output->set('error', 'Login failed');
        }

        return $output;
    }
}
