<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Controller;

use GyMadarasz\WebApp\Service\Config;
use GyMadarasz\WebApp\Service\Template;
use GyMadarasz\WebApp\Service\Mysql;
use GyMadarasz\WebApp\Service\User;
use GyMadarasz\WebApp\Service\Globals;

class ActivatePage
{
    private Config $config;
    private Template $template;
    private User $user;
    private Globals $globals;

    public function __construct(Config $config, Template $template, User $user, Globals $globals)
    {
        $this->config = $config;
        $this->template = $template;
        $this->user = $user;
        $this->globals = $globals;
    }

    /**
     * @return mixed
     */
    public function run()
    {
        if ($this->user->doActivate($this->globals->getGet('token'))) {
            $output = $this->template->create('login.html.php');
            $output->set('message', 'Your account is now activated.');
        } else {
            $output = $this->template->create('error-page.html.php');
            $output->set('error', 'Activation token is incorrect.');
            $output->set('base', $this->config->get('baseUrl'));
        }

        return $output;
    }
}
