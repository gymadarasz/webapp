<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Controller;

use GyMadarasz\WebApp\UserErrorException;
use GyMadarasz\WebApp\Service\Template;
use GyMadarasz\WebApp\Service\User;
use GyMadarasz\WebApp\Service\Globals;
use GyMadarasz\WebApp\Service\PasswordValidator;

class NewPasswordPage
{
    public function viewNewPassword(Template $template, User $user, Globals $globals): Template
    {
        $token = $globals->getGet('token');
        if (!$user->doAuthByToken($token)) {
            $output = $template->create('index.html.php', [
                'body' => 'pwdchange.html.php',
            ]);
            $output->set('error', 'Invalid token');
            return $output;
        }
        return $template->create('index.html.php', [
            'body' => 'pwdchange.html.php',
        ]);
    }

    // TODO needs more negative tests for new password posting
    public function doNewPassword(
        Template $template,
        User $user,
        Globals $globals,
        PasswordValidator $passwordValidator
    ): Template {
        $token = $globals->getGet('token');
        if (!$user->doAuthByToken($token)) {
            throw new UserErrorException('Invalid token');
        }
        $password = $globals->getPost('password');
        $passwordRetype = $globals->getPost('password_retype');
        $error = false;
        if ($password != $passwordRetype) {
            $error = 'Two password are not identical';
        }
        if ($passwordError = $passwordValidator->getPasswordError($password)) {
            $error = $passwordError;
        }
        if (!$error && $user->changePassword($password)) {
            $output = $template->create('index.html.php', [
                'body' => 'login.html.php',
            ]);
            $output->set('message', 'Your password changed, please log in');
        } else {
            $output = $template->create('index.html.php', [
                'body' => 'pwdchange.html.php',
            ]);
            $output->set('error', $error);
        }
        return $output;
    }
}
