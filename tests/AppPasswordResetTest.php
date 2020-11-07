<?php declare(strict_types = 1);
        
/**
 * PHP version 7.4
 *
 * @category  PHP
 * @package   GyMadarasz\Test
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */

namespace GyMadarasz\Test;

/**
 * AppPasswordResetTest
 *
 * @category  PHP
 * @package   GyMadarasz\Test
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class AppPasswordResetTest
{
    protected AppTest $appTest;
    
    /**
     * Method testWith
     *
     * @param \GyMadarasz\Test\AppTest $appTest appTest
     *
     * @return void
     */
    public function testWith(AppTest $appTest): void
    {
        $this->appTest = $appTest;
        
        $this->checkPasswordResetWorks();
    }
    
    /**
     * Method checkPasswordResetWorks
     *
     * @return void
     */
    protected function checkPasswordResetWorks(): void
    {
        $this->appTest->getLogger()->test('I am going to reset my password.');

        $contents = $this->appTest->getTester()->get('?q=pwdreset');
        $this->appTest->getAppChecker()->checkPasswordResetPage($contents);


        $this->appTest->getLogger()->test(
            'I am going to post my email address to Password Reset page.'
        );

        $contents = $this->appTest->getTester()->post(
            '?q=pwdreset',
            [
            'email' => AppTest::USER_EMAIL,
            ]
        );
        $this->appTest->getAppChecker()->checkLoginPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsMessage(
            $contents,
            'We sent an email to your inbox, '
                . 'please follow the given instructions to change your password'
        );


        $this->appTest->getLogger()->test(
            'I am going to check my emails for password reset instructions.'
        );
        
        $email = $this->appTest->checkMail('Password reset request');
        $token = $this->appTest->checkPasswordResetEmail($email);
        $this->appTest->cleanupMails();


        $this->appTest->getLogger()->test(
            'I am going to check the Password Reset page with an incorrect token.'
        );

        $contents = $this->appTest->getTester()->get(
            $this->appTest->getConfig()->get('baseUrl') .
                '?q=newpassword&token=invalid'
        );
        $this->appTest->getAppChecker()->checkChangePasswordPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsError(
            $contents,
            'Invalid token'
        );


        $this->appTest->getLogger()->test(
            'I am going to follow the Password Reset link '
                . 'to the Password Reset page.'
        );

        $contents = $this->appTest->getTester()->get(
            $this->appTest->getConfig()->get('baseUrl') .
                '?q=newpassword&token=' . $token
        );
        $this->appTest->getAppChecker()->checkChangePasswordPage($contents);


        $this->appTest->getLogger()->test(
            'I am going to post my new password '
            . 'to reset my password.'
        );

        $contents = $this->appTest->getTester()->post(
            $this->appTest->getConfig()->get('baseUrl') .
                '?q=newpassword&token=' . $token,
            [
            'password' => AppTest::USER_PASSWORD,
            'password_retype' => AppTest::USER_PASSWORD,
            ]
        );
        $this->appTest->getAppChecker()->checkLoginPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsMessage(
            $contents,
            'Your password changed, please log in'
        );
    }
}
