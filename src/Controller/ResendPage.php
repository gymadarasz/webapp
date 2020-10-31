<?php declare(strict_types = 1);

namespace Madsoft\App\Controller;

use Madsoft\App\UserErrorException;
use Madsoft\App\Service\Template;
use Madsoft\App\Service\Config;
use Madsoft\App\Service\Globals;
use Madsoft\App\Service\Mailer;

class ResendPage
{
    private Template $template;
    private Config $config;
    private Globals $globals;
    private Mailer $mailer;

    public function __construct(
        Template $template,
        Config $config,
        Globals $globals,
        Mailer $mailer
    ) {
        $this->template = $template;
        $this->config = $config;
        $this->globals = $globals;
        $this->mailer = $mailer;
    }

    /**
     * @return mixed
     */
    public function run()
    {
        $output = $this->template->create('login.html.php');
        $output->setAsItIs('message', 'Attempt to resend activation email, please check your email inbox and validate your account, or try to resend by <a href="?q=resend">click here</a>');

        $resend = $this->globals->getSession('resend');
        $email = $resend['email'];
        $token = $resend['token'];
        if (!$this->sendActivationEmail($email, $token)) {
            throw new UserErrorException('Email sending error!');
        }

        return $output;
    }

    private function sendActivationEmail(string $email, string $token): bool
    {
        $message = $this->template->create('emails/activation.html.php');
        $message->setAsItIs('link', $this->config->get('baseUrl') . "?q=activate&token=$token");
        return $this->mailer->send($email, 'Activate your account', (string)$message);
    }
}
