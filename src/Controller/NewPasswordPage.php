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
use GyMadarasz\WebApp\Service\User;
use GyMadarasz\WebApp\Service\Globals;
use GyMadarasz\WebApp\Service\PasswordValidator;

/**
 * NewPasswordPage
 *
 * @category  PHP
 * @package   GyMadarasz\WebApp\Controller
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class NewPasswordPage
{
    /**
     * Method viewNewPassword
     *
     * @param Template $template template
     * @param User     $user     user
     * @param Globals  $globals  globals
     *
     * @return Template
     */
    public function viewNewPassword(
        Template $template,
        User $user,
        Globals $globals
    ): Template {
        $token = $globals->getGet('token');
        if (!$user->doAuthByToken($token)) {
            $output = $template->create(
                'index.html',
                [
                'body' => 'pwdchange.html',
                ]
            );
            $output->set('error', 'Invalid token');
            return $output;
        }
        return $template->create(
            'index.html',
            [
            'body' => 'pwdchange.html',
            ]
        );
    }

    // TODO needs more negative tests for new password posting
    
    /**
     * Method doNewPassword
     *
     * @param Template          $template          template
     * @param User              $user              user
     * @param Globals           $globals           globals
     * @param PasswordValidator $passwordValidator passwordValidator
     *
     * @return Template
     * @throws UserErrorException
     */
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
            $output = $template->create(
                'index.html',
                [
                'body' => 'login.html',
                ]
            );
            $output->set('message', 'Your password changed, please log in');
        } else {
            $output = $template->create(
                'index.html',
                [
                'body' => 'pwdchange.html',
                ]
            );
            $output->set('error', $error);
        }
        return $output;
    }
}
