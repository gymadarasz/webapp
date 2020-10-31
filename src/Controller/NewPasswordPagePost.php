<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Controller;

use GyMadarasz\WebApp\UserErrorException;
use GyMadarasz\WebApp\Service\Template;
use GyMadarasz\WebApp\Service\Mysql;
use GyMadarasz\WebApp\Service\User;
use GyMadarasz\WebApp\Service\Globals;
use GyMadarasz\WebApp\Service\PasswordValidator;

class NewPasswordPagePost
{
    private Template $template;
    private Mysql $mysql;
    private User $user;
    private Globals $globals;
    private PasswordValidator $passwordValidator;

    public function __construct(
        Template $template,
        Mysql $mysql,
        User $user,
        Globals $globals,
        PasswordValidator $passwordValidator
    ) {
        $this->template = $template;
        $this->mysql = $mysql;
        $this->user = $user;
        $this->globals = $globals;
        $this->passwordValidator = $passwordValidator;
    }

    /**
     * @return mixed
     */
    public function run() // TODO needs more negative tests for new password posting
    {
        $token = $this->globals->getGet('token');
        $this->mysql->connect();
        if (!$this->user->doAuthByToken($token)) {
            throw new UserErrorException('Invalid token');
        }
        $password = $this->globals->getPost('password');
        $passwordRetype = $this->globals->getPost('password_retype');
        $error = false;
        if ($password != $passwordRetype) {
            $error = 'Two password are not identical';
        }
        if ($passwordError = $this->passwordValidator->getPasswordError($password)) {
            $error = $passwordError;
        }
        if (!$error && $this->user->changePassword($password)) {
            $output = $this->template->create('login.html.php');
            $output->set('message', 'Your password changed, please log in');
        } else {
            $output = $this->template->create('pwdchange.html.php');
            $output->set('error', $error);
        }
        return $output;
    }
}
