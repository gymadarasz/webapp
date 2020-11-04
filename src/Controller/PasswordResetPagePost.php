<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Controller;

use GyMadarasz\WebApp\UserErrorException;
use GyMadarasz\WebApp\Service\Template;
use GyMadarasz\WebApp\Service\Config;
use GyMadarasz\WebApp\Service\User;
use GyMadarasz\WebApp\Service\Globals;
use GyMadarasz\WebApp\Service\Mailer;
use GyMadarasz\WebApp\Service\EmailValidator;

class PasswordResetPagePost
{
    private Template $template;
    private Config $config;
    private User $user;
    private Globals $globals;
    private Mailer $mailer;
    private EmailValidator $emailValidator;

    public function __construct(
        Template $template,
        Config $config,
        User $user,
        Globals $globals,
        Mailer $mailer,
        EmailValidator $emailValidator
    ) {
        $this->template = $template;
        $this->config = $config;
        $this->user = $user;
        $this->globals = $globals;
        $this->mailer = $mailer;
        $this->emailValidator = $emailValidator;
    }

    public function doPasswordReset(): Template
    {
        $output = $this->template->create('index.html.php', [
            'body' => 'login.html.php',
        ]);
        if ($this->resetPassword($this->globals->getPost('email', ''))) {
            $output->set('message', 'We sent an email to your inbox, please follow the given instructions to change your password');
        } else {
            $output->set('error', 'Request for password reset is failed');
        }

        return $output;
    }

    private function resetPassword(string $email): bool
    {
        if (!$this->emailValidator->isValidEmail($email)) {
            return false;
        }
        $token = $this->user->createToken($email);
        if (!$token) {
            return false;
        }
        if (!$this->sendPasswordResetEmail($email, $token)) {
            return false;
        }
        return true;
    }

    private function sendPasswordResetEmail(string $email, string $token): bool
    {
        $message = $this->template->create('emails/pwd-reset.html.php');
        $message->setAsItIs('link', $this->config->get('baseUrl') . "?q=newpassword&token=$token");
        return $this->mailer->send($email, 'Password reset request', (string)$message);
    }
}
