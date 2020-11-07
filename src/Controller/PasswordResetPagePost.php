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

use GyMadarasz\WebApp\UserErrorException;
use GyMadarasz\WebApp\Service\Template;
use GyMadarasz\WebApp\Service\Config;
use GyMadarasz\WebApp\Service\User;
use GyMadarasz\WebApp\Service\Globals;
use GyMadarasz\WebApp\Service\Mailer;
use GyMadarasz\WebApp\Service\EmailValidator;

/**
 * PasswordResetPagePost
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Controller
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class PasswordResetPagePost
{
    protected Template $template;
    protected Config $config;
    protected User $user;
    protected Globals $globals;
    protected Mailer $mailer;
    protected EmailValidator $emailValidator;

    /**
     * Method __construct
     *
     * @param Template       $template       template
     * @param Config         $config         config
     * @param User           $user           user
     * @param Globals        $globals        globals
     * @param Mailer         $mailer         mailer
     * @param EmailValidator $emailValidator emailValidator
     */
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

    /**
     * Method doPasswordReset
     *
     * @return Template
     */
    public function doPasswordReset(): Template
    {
        $output = $this->template->create(
            'index.html.php',
            [
            'body' => 'login.html.php',
            ]
        );
        if ($this->resetPassword($this->globals->getPost('email', ''))) {
            $output->set(
                'message',
                'We sent an email to your inbox, '
                    . 'please follow the given instructions to change your password'
            );
        } else {
            $output->set('error', 'Request for password reset is failed');
        }

        return $output;
    }

    /**
     * Method resetPassword
     *
     * @param string $email email
     *
     * @return bool
     */
    protected function resetPassword(string $email): bool
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

    /**
     * Method sendPasswordResetEmail
     *
     * @param string $email email
     * @param string $token token
     *
     * @return bool
     */
    protected function sendPasswordResetEmail(string $email, string $token): bool
    {
        $message = $this->template->create('emails/pwd-reset.html.php');
        $message->setAsItIs(
            'link',
            $this->config->get('baseUrl') . "?q=newpassword&token=$token"
        );
        return $this->mailer->send(
            $email,
            'Password reset request',
            (string)$message
        );
    }
}
