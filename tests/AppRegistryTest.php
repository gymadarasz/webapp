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
 * AppRegistryTest
 *
 * @category  PHP
 * @package   GyMadarasz\Test
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class AppRegistryTest
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
        
        $this->checkIncorrectLogin();
        $this->checkRegistryPageWorks();
        $this->checkActivationEmailResend();
        $this->checkLoginLogoutWorks();
    }
    
    /**
     * Method checkIncorrectLogin
     *
     * @return void
     */
    protected function checkIncorrectLogin(): void
    {
        $this->appTest->getLogger()->test('I am going to the Login page.');

        $contents = $this->appTest->getTester()->get('');
        $this->appTest->getAppChecker()->checkLoginPage($contents);


        $this->appTest->getLogger()->test(
            'I am posting my account, but it should fails because '
                . 'I am not registered.'
        );

        $contents = $this->appTest->getTester()->post(
            '',
            [
            'email' => AppTest::USER_EMAIL,
            'password' => AppTest::USER_PASSWORD_OLD,
            ]
        );
        $this->appTest->getAppChecker()->checkLoginPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsError(
            $contents,
            'Login failed'
        );
    }

    /**
     * Method checkRegistryPageWorks
     *
     * @return void
     */
    protected function checkRegistryPageWorks(): void
    {
        $this->appTest->getLogger()->test('I am going to Register page.');

        $contents = $this->appTest->getTester()->get('?q=registry');
        $this->appTest->getAppChecker()->checkRegistryPage($contents);


        $this->appTest->getLogger()->test(
            'I am posting my registry details to the Register page.'
        );

        $contents = $this->appTest->getTester()->post(
            '?q=registry',
            [
            'email' => AppTest::USER_EMAIL,
            'email_retype' => AppTest::USER_EMAIL,
            'password' => AppTest::USER_PASSWORD_OLD,
            ]
        );
        $this->appTest->getAppChecker()->checkLoginPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsMessage(
            $contents,
            'Registration success, '
                . 'please check your email inbox and validate your account, '
                . 'or try to resend by <a href="?q=resend">click here</a>'
        );
    }
    
    /**
     * Method checkActivationEmailResend
     *
     * @return void
     */
    protected function checkActivationEmailResend(): void
    {
        $this->appTest->getLogger()->test(
            'I am going to check my activation email.'
        );

        $email = $this->appTest->checkMail('Activate your account');
        $token = $this->appTest->checkRegistrationEmail($email);
        $this->appTest->cleanupMails();


        $this->appTest->getLogger()->test('I am going to click on resend link.');

        $contents = $this->appTest->getTester()->get('?q=resend');
        $this->appTest->getAppChecker()->checkLoginPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsMessage(
            $contents,
            'Attempt to resend activation email, '
                . 'please check your email inbox and validate your account, '
                . 'or try to resend by <a href="?q=resend">click here</a>'
        );


        $this->appTest->getLogger()->test(
            'I am posting my account details to Login page, '
                . 'but it should fails because '
                . 'I am not activated my newly registered account.'
        );

        $contents = $this->appTest->getTester()->post(
            '',
            [
            'email' => AppTest::USER_EMAIL,
            'password' => AppTest::USER_PASSWORD_OLD,
            ]
        );
        $this->appTest->getAppChecker()->checkLoginPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsError(
            $contents,
            'Login failed'
        );


        $this->appTest->getLogger()->test(
            'I am going to check my activation email.'
        );

        $email = $this->appTest->checkMail('Activate your account');
        $token = $this->appTest->checkRegistrationEmail($email);
        $this->appTest->cleanupMails();


        $this->appTest->getLogger()->test(
            'I am going to try to activate my account '
                . 'with an incorrect activation token.'
        );

        $contents = $this->appTest->getTester()->get(
            $this->appTest->getConfig()->get('baseUrl') .
                '?q=activate&token=incorrect'
        );
        $this->appTest->getAppChecker()->checkErrorPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsError(
            $contents,
            'Activation token is incorrect.'
        );


        $this->appTest->getLogger()->test(
            'I am going to activate my account with the correct activation token.'
        );

        $contents = $this->appTest->getTester()->get(
            $this->appTest->getConfig()->get('baseUrl') .
                '?q=activate&token=' . $token
        );
        $this->appTest->getAppChecker()->checkLoginPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsMessage(
            $contents,
            'Your account is now activated.'
        );


        $this->appTest->getLogger()->test(
            'I am just check if I am activated in the database properly?'
        );
        $results = $this->appTest->getMysql()->selectOne(
            "SELECT active FROM user WHERE email = '" .
                AppTest::USER_EMAIL . "' LIMIT 1;"
        );
        $this->appTest->getTester()->getAssertor()->assertTrue(
            isset($results['active'])
        );
        $this->appTest->getTester()->getAssertor()->assertEquals(
            1,
            (int)((array)$results)['active']
        );
    }
    
    /**
     * Method checkLoginLogoutWorks
     *
     * @return void
     */
    protected function checkLoginLogoutWorks(): void
    {
        $this->appTest->getLogger()->test(
            'I am going to post my newly registered account details.'
        );
        $contents = $this->appTest->checkIfICanLogin(
            AppTest::USER_EMAIL,
            AppTest::USER_PASSWORD_OLD
        );
        $this->appTest->getAppChecker()->checkMainPage($contents);
        

        $this->appTest->getLogger()->test(
            'I am going to the restricted Index page.'
        );

        $contents = $this->appTest->getTester()->get('');
        $this->appTest->getAppChecker()->checkMainPage($contents);


        $this->appTest->getLogger()->test('I am going to Logout.');

        $contents = $this->appTest->getTester()->get('?q=logout');
        $this->appTest->getAppChecker()->checkLoginPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsMessage(
            $contents,
            'Logout success'
        );
    }
}
