<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Controller;

use GyMadarasz\WebApp\Service\Template;
use GyMadarasz\WebApp\Service\Globals;

class LogoutPage
{
    public function viewLogout(Template $template, Globals $globals): Template
    {
        $globals->sessionDestroy();
        $output = $template->create(
            'index.html.php',
            [
            'body' => 'login.html.php',
            ]
        );
        $output->set('message', 'Logout success');

        return $output;
    }
}
