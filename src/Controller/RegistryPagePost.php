<?php declare(strict_types = 1);

/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Controller
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */

namespace GyMadarasz\WebApp\Controller;

// TODO avoid user error exception (and all exception where its possible)
use GyMadarasz\WebApp\UserErrorException;
use GyMadarasz\WebApp\Service\Config;
use GyMadarasz\WebApp\Service\Template;
use GyMadarasz\WebApp\Service\User;
use GyMadarasz\WebApp\Service\Globals;
use GyMadarasz\WebApp\Service\Logger;
use GyMadarasz\WebApp\Service\Mailer;
use GyMadarasz\WebApp\Service\EmailValidator;
use GyMadarasz\WebApp\Service\PasswordValidator;
use Exception;

/**
 * RegistryPagePost
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Controller
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class RegistryPagePost
{
    protected Template $template;
    protected Config $config;
    protected User $user;
    protected Globals $globals;
    protected Logger $logger;
    protected Mailer $mailer;
    protected EmailValidator $emailValidator;
    protected PasswordValidator $passwordValidator;

    /**
     * Method __construct
     *
     * @param Template          $template          template
     * @param Config            $config            config
     * @param User              $user              user
     * @param Globals           $globals           globals
     * @param Logger            $logger            logger
     * @param Mailer            $mailer            mailer
     * @param EmailValidator    $emailValidator    emailValidator
     * @param PasswordValidator $passwordValidator passwordValidator
     */
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

    /**
     * Method doRegistry
     *
     * @return Template
     */
    public function doRegistry(): Template
    {
        try {
            $this->registry(
                $this->globals->getPost('email', ''),
                $this->globals->getPost('email_retype', ''),
                $this->globals->getPost('password', '')
            );
            $output = $this->template->create(
                'index.html.php',
                [
                'body' => 'login.html.php',
                ]
            );
            $output->setAsItIs(
                'message',
                'Registration success, '
                    . 'please check your email inbox and validate your account, '
                    . 'or try to resend by <a href="?q=resend">click here</a>'
            );
        } catch (UserErrorException $e) {
            $output = $this->template->create(
                'index.html.php',
                [
                'body' => 'registry.html.php',
                ]
            );
            $output->set('error', $e->getMessage());
            $output->set('email', $this->globals->getPost('email'));
            $output->set('emailRetype', $this->globals->getPost('email_retype'));
        } catch (Exception $e) {
            $this->logger->doLogException($e);
            $output = $this->template->create(
                'index.html.php',
                [
                'body' => 'registry.html.php',
                ]
            );
            $output->set('error', 'Registration failed');
        }

        return $output;
    }

    /**
     * Method registry
     *
     * @param string $email       email
     * @param string $emailRetype emailRetype
     * @param string $password    password
     *
     * @return void
     * @throws UserErrorException
     */
    protected function registry(
        string $email,
        string $emailRetype,
        string $password
    ): void {
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
        $this->globals->setSession(
            'resend',
            [
            'email' => $email,
            'token' => $token,
            ]
        );
        if (!$token) {
            throw new UserErrorException('Registration error!');
        }
        if (!$this->sendActivationEmail($email, $token)) {
            throw new UserErrorException('Email sending error!');
        }
    }

    /**
     * Method sendActivationEmail
     *
     * @param string $email email
     * @param string $token token
     *
     * @return bool
     */
    protected function sendActivationEmail(string $email, string $token): bool
    {
        $message = $this->template->create('emails/activation.html.php');
        $message->setAsItIs(
            'link',
            $this->config->get('baseUrl') . "?q=activate&token=$token"
        );
        return $this->mailer->send(
            $email,
            'Activate your account',
            (string)$message
        );
    }
}
