<?php declare(strict_types = 1);

namespace Madsoft\App\Controller;

use Madsoft\App\Service\Config;
use Madsoft\App\Service\Template;
use Madsoft\App\Service\Mysql;
use Madsoft\App\Service\User;
use Madsoft\App\Service\Globals;

class ActivatePage
{
    private Config $config;
    private Template $template;
    private Mysql $mysql;
    private User $user;
    private Globals $globals;

    public function __construct(Config $config, Template $template, Mysql $mysql, User $user, Globals $globals)
    {
        $this->config = $config;
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
