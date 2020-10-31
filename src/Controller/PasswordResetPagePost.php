<?php declare(strict_types = 1);

namespace Madsoft\App\Controller;

use Madsoft\App\UserErrorException;
use Madsoft\App\Service\Template;
use Madsoft\App\Service\Config;
use Madsoft\App\Service\Mysql;
use Madsoft\App\Service\User;
use Madsoft\App\Service\Globals;
use Madsoft\App\Service\Mailer;
use Madsoft\App\Service\EmailValidator;

class PasswordResetPagePost
{
    private Template $template;
    private Config $config;
    private Mysql $mysql;
    private User $user;
    private Globals $globals;
    private Mailer $mailer;
    private EmailValidator $emailValidator;

    public function __construct(
        Template $template,
        Config $config,
        Mysql $mysql,
        User $user,
        Globals $globals,
        Mailer $mailer,
        EmailValidator $emailValidator
    ) {
        $this->template = $template;
        $this->config = $config;
        $this->mysql = $mysql;
        $this->user = $user;
        $this->globals = $globals;
        $this->mailer = $mailer;
        $this->emailValidator = $emailValidator;
    }

    /**
     * @return mixed
     */
    public function run()
    {
        $output = $this->template->create('login.html.php');
        if ($this->doPasswordReset($this->globals->getPost('email', ''))) {
            $output->set('message', 'We sent an email to your inbox, please follow the given instructions to change your password');
        } else {
            $output->set('error', 'Request for password reset is failed');
        }

        return $output;
    }

    private function doPasswordReset(string $email): bool
    {
        if (!$this->emailValidator->isValidEmail($email)) {
            return false;
        }
        $this->mysql->connect();
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
