<?php declare(strict_types = 1);

namespace Madsoft\App\Controller;

use Madsoft\App\UserErrorException;
use Madsoft\App\Service\Template;
use Madsoft\App\Service\Mysql;
use Madsoft\App\Service\User;
use Madsoft\App\Service\Globals;
use Madsoft\App\Service\PasswordValidator;

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
    public function run()
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
