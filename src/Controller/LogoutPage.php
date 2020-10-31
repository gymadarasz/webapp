<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Controller;

use GyMadarasz\WebApp\Service\Template;
use GyMadarasz\WebApp\Service\Globals;

class LogoutPage
{
    private Template $template;
    private Globals $globals;

    public function __construct(Template $template, Globals $globals)
    {
        $this->template = $template;
        $this->globals = $globals;
    }

    /**
     * @return mixed
     */
    public function run()
    {
        $this->globals->sessionDestroy();
        $output = $this->template->create('login.html.php');
        $output->set('message', 'Logout success');

        return $output;
    }
}
