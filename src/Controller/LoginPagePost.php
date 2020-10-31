<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Controller;

use GyMadarasz\WebApp\Service\Template;
use GyMadarasz\WebApp\Service\Mysql;
use GyMadarasz\WebApp\Service\User;
use GyMadarasz\WebApp\Service\Globals;

class LoginPagePost
{
    private Template $template;
    private Mysql $mysql;
    private User $user;
    private Globals $globals;

    public function __construct(Template $template, Mysql $mysql, User $user, Globals $globals)
    {
        $this->template = $template;
        $this->mysql = $mysql;
        $this->user = $user;
        $this->globals = $globals;
    }

    /**
     * @return mixed
     */
    public function run()
    {
        $this->mysql->connect();
        if ($this->user->doAuth(
            $this->globals->getPost('email', ''),
            $this->globals->getPost('password', '')
        )) {
            $output = $this->template->create('index.html.php');
            $output->set('message', 'Login success');
        } else {
            $output = $this->template->create('login.html.php');
            $output->set('error', 'Login failed');
        }

        return $output;
    }
}
