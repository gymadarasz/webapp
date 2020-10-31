<?php declare(strict_types = 1);

namespace Madsoft\App\Controller;

use Madsoft\App\UserErrorException;
use Madsoft\App\Service\Template;
use Madsoft\App\Service\Mysql;
use Madsoft\App\Service\User;
use Madsoft\App\Service\Globals;

class NewPasswordPage
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
        $token = $this->globals->getGet('token');
        $this->mysql->connect();
        if (!$this->user->doAuthByToken($token)) {
            throw new UserErrorException('Invalid token');
        }
        return $this->template->create('pwdchange.html.php');
    }
}
