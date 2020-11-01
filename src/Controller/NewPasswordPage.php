<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Controller;

use GyMadarasz\WebApp\UserErrorException;
use GyMadarasz\WebApp\Service\Template;
use GyMadarasz\WebApp\Service\Mysql;
use GyMadarasz\WebApp\Service\User;
use GyMadarasz\WebApp\Service\Globals;

class NewPasswordPage
{
    private Template $template;
    private User $user;
    private Globals $globals;

    public function __construct(Template $template, User $user, Globals $globals)
    {
        $this->template = $template;
        $this->user = $user;
        $this->globals = $globals;
    }

    /**
     * @return mixed
     */
    public function run()
    {
        $token = $this->globals->getGet('token');
        if (!$this->user->doAuthByToken($token)) {
            $output = $this->template->create('pwdchange.html.php');
            $output->set('error', 'Invalid token');
            return $output;
        }
        return $this->template->create('pwdchange.html.php');
    }
}
