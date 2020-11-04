<?php declare(strict_types = 1);

namespace GyMadarasz\WebApp\Controller;

use GyMadarasz\WebApp\UserErrorException; // TODO avoid user error exception (and all exception where its possible)
use GyMadarasz\WebApp\Service\Config;
use GyMadarasz\WebApp\Service\Template;
use GyMadarasz\WebApp\Service\User;
use GyMadarasz\WebApp\Service\Globals;
use GyMadarasz\WebApp\Service\Logger;
use GyMadarasz\WebApp\Service\Mailer;
use GyMadarasz\WebApp\Service\EmailValidator;
use GyMadarasz\WebApp\Service\PasswordValidator;
use Exception;

class RegistryPagePost
{
    private Template $template;
    private Config $config;
    private User $user;
    private Globals $globals;
    private Logger $logger;
    private Mailer $mailer;
    private EmailValidator $emailValidator;
    private PasswordValidator $passwordValidator;

    public function __construct(
        Template $template,
        Config $config,
        User $user,
        Globals $globals,
        Logger $logger,
        Mailer $mailer,
        EmailValidator $emailValidator,
        PasswordValidator $passwordValidator
    ) {
        $this->template = $template;
        $this->config = $config;
        $this->user = $user;
        $this->globals = $globals;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->emailValidator = $emailValidator;
        $this->passwordValidator = $passwordValidator;
    }

    public function doRegistry(): Template
    {
        try {
            $this->registry(
                $this->globals->getPost('email', ''),
                $this->globals->getPost('email_retype', ''),
                $this->globals->getPost('password', '')
            );
            $output = $this->template->create('index.html.php', [
                'body' => 'login.html.php',
            ]);
            $output->setAsItIs('message', 'Registration success, please check your email inbox and validate your account, or try to resend by <a href="?q=resend">click here</a>');
        } catch (UserErrorException $e) {
            $output = $this->template->create('index.html.php', [
                'body' => 'registry.html.php',
            ]);
            $output->set('error', $e->getMessage());
            $output->set('email', $this->globals->getPost('email'));
            $output->set('emailRetype', $this->globals->getPost('email_retype'));
        } catch (Exception $e) {
            $this->logger->doLogException($e);
            $output = $this->template->create('index.html.php', [
                'body' => 'registry.html.php',
            ]);
            $output->set('error', 'Registration failed');
        }

        return $output;
    }

    private function registry(string $email, string $emailRetype, string $password): void
    {
        if (!$email) {
            throw new UserErrorException('Email can not be empty');
        }
        if ($email !== $emailRetype) {
            throw new UserErrorException('Email fields are not the same');
        }
        if (!$this->emailValidator->isValidEmail($email)) {
            throw new UserErrorException('Email address is invalid');
        }
        if ($passwordError = $this->passwordValidator->getPasswordError($password)) {
            throw new UserErrorException($passwordError);
        }
        $token = $this->user->createUser($email, $password);
        $this->globals->setSession('resend', [
            'email' => $email,
            'token' => $token,
        ]);
        if (!$token) {
            throw new UserErrorException('Registration error!');
        }
        if (!$this->sendActivationEmail($email, $token)) {
            throw new UserErrorException('Email sending error!');
        }
    }

    private function sendActivationEmail(string $email, string $token): bool
    {
        $message = $this->template->create('emails/activation.html.php');
        $message->setAsItIs('link', $this->config->get('baseUrl') . "?q=activate&token=$token");
        return $this->mailer->send($email, 'Activate your account', (string)$message);
    }
}
