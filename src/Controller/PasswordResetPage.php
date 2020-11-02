<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Controller;

use GyMadarasz\WebApp\Service\Template;

class PasswordResetPage
{
    public function viewPasswordReset(Template $template): Template
    {
        return $template->create('pwdreset.html.php');
    }
}
