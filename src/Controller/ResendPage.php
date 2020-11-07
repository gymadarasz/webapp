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
use GyMadarasz\WebApp\Service\Globals;
use GyMadarasz\WebApp\Service\Mailer;

/**
 * ResendPage
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Controller
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class ResendPage
{
    protected Template $template;
    protected Config $config;
    protected Globals $globals;
    protected Mailer $mailer;

    /**
     * Method __construct
     *
     * @param Template $template template
     * @param Config   $config   config
     * @param Globals  $globals  globals
     * @param Mailer   $mailer   mailer
     */
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
     * Method viewResend
     *
     * @return Template
     * @throws UserErrorException
     */
    public function viewResend(): Template
    {
        $output = $this->template->create(
            'index.html',
            [
            'body' => 'login.html',
            ]
        );
        $output->setAsItIs(
            'message',
            'Attempt to resend activation email, '
                . 'please check your email inbox and validate your account, '
                . 'or try to resend by <a href="?q=resend">click here</a>'
        );

        $resend = $this->globals->getSession('resend');
        $email = $resend['email'];
        $token = $resend['token'];
        if (!$this->sendActivationEmail($email, $token)) {
            throw new UserErrorException('Email sending error!');
        }

        return $output;
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
        $message = $this->template->create('emails/activation.html');
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
