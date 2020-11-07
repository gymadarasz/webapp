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

use GyMadarasz\WebApp\Service\Config;
use GyMadarasz\WebApp\Service\Logger;
use GyMadarasz\WebApp\Service\Mysql;

/**
 * AppTest
 *
 * @category  PHP
 * @package   GyMadarasz\Test
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class AppTest
{
    const USER_EMAIL = 'tester@example.com';
    const USER_PASSWORD_OLD = 'OldPassword123!';
    const USER_PASSWORD = 'Pass1234';

    protected Tester $tester;
    protected Config $config;
    protected Logger $logger;
    protected Mysql $mysql;

    /**
     * Method cleanupUsers
     *
     * @return void
     */
    protected function cleanupUsers(): void
    {
        $this->logger->test('I am going to delete all users from database.');

        $this->mysql->query(
            "DELETE FROM user WHERE email = '" . AppTest::USER_EMAIL . "';"
        );
    }

    /**
     * Method cleanupMails
     *
     * @return void
     */
    public function cleanupMails(): void
    {
        $this->logger->test('I am going to delete all saved emails.');

        $files = (array)glob($this->config->get('mailerSaveMailsPath') . '/*.*');
        foreach ($files as $file) {
            unlink((string)$file);
        }
    }

    /**
     * Method cleanup
     *
     * @return void
     */
    protected function cleanup(): void
    {
        $this->cleanupUsers();
        $this->cleanupMails();
    }

    /**
     * Method test
     *
     * @param Tester          $tester  tester
     * @param Config          $config  config
     * @param Logger          $logger  logger
     * @param Mysql           $mysql   mysql
     * @param AppRegistryTest $regTest regTest
     *
     * @return void
     */
    public function test(
        Tester $tester,
        Config $config,
        Logger $logger,
        Mysql $mysql,
        AppRegistryTest $regTest,
        AppPasswordResetTest $pwdResetTest,
        AppErrorPageTest $errPageTest
    ): void {
        $this->tester = $tester;
        $this->config = $config;
        $this->logger = $logger;
        $this->mysql = $mysql;
        
        // ----- clean up -----
        $this->cleanup();

        // ------- tests ----------
        $regTest->testWith($this);
        $pwdResetTest->testWith($this);
        $errPageTest->testWith($this);
        $this->checkLoginAfterLoginShouldFails();
        $this->checkInvalidRegistryShouldFails();
        $this->checkInvalidPasswordResetShouldFails();

        // ----- clean up -----
        //$this->cleanup();
    }
    
    /**
     * Method checkIfICanLogin
     *
     * @param string $user     user
     * @param string $password password
     *
     * @return string
     */
    public function checkIfICanLogin(
        string $user = AppTest::USER_EMAIL,
        string $password = AppTest::USER_PASSWORD
    ): string {
        $this->logger->test(
            'I am going to post these login credentials: '
                . "user: '$user' / password: '$password'"
        );

        $contents = $this->tester->post(
            '?q=',
            [
            'email' => $user,
            'password' => $password,
            ]
        );
        $this->checkPageContainsMessage($contents, 'Login success');
        return $contents;
    }

    /**
     * Method checkLoginAfterLoginShouldFails
     *
     * @return void
     */
    protected function checkLoginAfterLoginShouldFails(): void
    {
        $this->logger->test('I am going to login');
        $contents = $this->checkIfICanLogin();
        $this->checkMainPage($contents);


        $this->logger->test(
            'I am going to try login again '
                . 'when I am logged in so that I get an error page.'
        );

        $contents = $this->tester->post(
            '',
            [
            'email' => AppTest::USER_EMAIL,
            'password' => AppTest::USER_PASSWORD,
            ]
        );
        $this->checkErrorPage($contents);
        $this->checkPageContainsError($contents, 'Request is not supported.');


        $this->logger->test('I am going to Logout.');

        $contents = $this->tester->get('?q=logout');
        $this->checkLoginPage($contents);
        $this->checkPageContainsMessage($contents, 'Logout success');
    }
    
    protected function checkEmptyRegistrationValidation(): void
    {
        $this->logger->test('I am going to send an empty registration');

        $contents = $this->tester->post(
            '?q=registry',
            [
            'email' => '',
            'email_retype' => '',
            'password' => '',
            ]
        );
        $this->checkRegistryPage($contents);
        $this->checkPageContainsError($contents, 'Email can not be empty');
    }
    
    protected function checkTwoEmailNotMatchValidation(): void
    {
        $this->logger->test('I am going to send two different email address');

        $contents = $this->tester->post(
            '?q=registry',
            [
            'email' => 'email@address.org',
            'email_retype' => 'email@misspelled.org',
            'password' => '',
            ]
        );
        $this->checkRegistryPage($contents);
        $this->checkPageContainsError($contents, 'Email fields are not the same');
    }
    
    protected function checkInvalidEmailValidation(): void
    {
        $this->logger->test('I am going to send an invalid email address');

        $contents = $this->tester->post(
            '?q=registry',
            [
            'email' => 'invalid-address.org',
            'email_retype' => 'invalid-address.org',
            'password' => '',
            ]
        );
        $this->checkRegistryPage($contents);
        $this->checkPageContainsError($contents, 'Email address is invalid');
    }
    
    protected function checkShortPasswordValidation(): void
    {
        $this->logger->test('I am going to send a short password');

        $contents = $this->tester->post(
            '?q=registry',
            [
            'email' => 'valid@address.org',
            'email_retype' => 'valid@address.org',
            'password' => 'a',
            ]
        );
        $this->checkRegistryPage($contents);
        $this->checkPageContainsError(
            $contents,
            'Your Password Must Contain At Least 8 Characters!'
        );
    }
    
    protected function checkPasswordWithoutNumber(): void
    {
        $this->logger->test('I am going to send a password without any number');

        $contents = $this->tester->post(
            '?q=registry',
            [
            'email' => 'valid@address.org',
            'email_retype' => 'valid@address.org',
            'password' => 'asdfghjkl',
            ]
        );
        $this->checkRegistryPage($contents);
        $this->checkPageContainsError(
            $contents,
            'Your Password Must Contain At Least 1 Number!'
        );
    }
    
    protected function checkPasswordWithoutCapitalValidation(): void
    {
        $this->logger->test(
            'I am going to send a password without any capital letter'
        );

        $contents = $this->tester->post(
            '?q=registry',
            [
            'email' => 'valid@address.org',
            'email_retype' => 'valid@address.org',
            'password' => 'asdfghjkl1234',
            ]
        );
        $this->checkRegistryPage($contents);
        $this->checkPageContainsError(
            $contents,
            'Your Password Must Contain At Least 1 Capital Letter!'
        );
    }
    
    protected function checkPasswordWithoutLowercaseValidation(): void
    {
        $this->logger->test(
            'I am going to send a password without any Lowercase Letter'
        );

        $contents = $this->tester->post(
            '?q=registry',
            [
            'email' => 'valid@address.org',
            'email_retype' => 'valid@address.org',
            'password' => 'QWERTYU1234',
            ]
        );
        $this->checkRegistryPage($contents);
        $this->checkPageContainsError(
            $contents,
            'Your Password Must Contain At Least 1 Lowercase Letter!'
        );
    }
    
    protected function checkUserAlreadyRediteredValidation(): void
    {
        $this->logger->test('I am going to try to registrate an already exist user');

        $contents = $this->tester->post(
            '?q=registry',
            [
            'email' => AppTest::USER_EMAIL,
            'email_retype' => AppTest::USER_EMAIL,
            'password' => 'ValidPassword123!',
            ]
        );
        $this->checkRegistryPage($contents);
        $this->checkPageContainsError($contents, 'Registration failed');
    }
    
    /**
     * Method checkInvalidRegistryShouldFails
     *
     * @return void
     */
    protected function checkInvalidRegistryShouldFails(): void
    {
        $this->checkEmptyRegistrationValidation();
        $this->checkTwoEmailNotMatchValidation();
        $this->checkInvalidEmailValidation();
        $this->checkShortPasswordValidation();
        $this->checkPasswordWithoutNumber();
        $this->checkPasswordWithoutCapitalValidation();
        $this->checkPasswordWithoutLowercaseValidation();
        $this->checkUserAlreadyRediteredValidation();
    }

    /**
     * Method checkInvalidPasswordResetShouldFails
     *
     * @return void
     */
    protected function checkInvalidPasswordResetShouldFails(): void
    {
        $this->logger->test(
            'I am going to try to send an empty email to password reset.'
        );

        $contents = $this->tester->post(
            '?q=pwdreset',
            [
            'email' => '',
            ]
        );
        $this->checkLoginPage($contents);
        $this->checkPageContainsError(
            $contents,
            'Request for password reset is failed'
        );


        $this->logger->test(
            'I am going to try to send an invalid email to password reset.'
        );

        $contents = $this->tester->post(
            '?q=pwdreset',
            [
            'email' => 'invalid-email-address',
            ]
        );
        $this->checkLoginPage($contents);
        $this->checkPageContainsError(
            $contents,
            'Request for password reset is failed'
        );
    }

    /**
     * Method checkErrorPage
     *
     * @param string $contents contents
     *
     * @return void
     */
    public function checkErrorPage(string $contents): void
    {
        $this->logger->test(
            'I am going to check that I can see the Error page properly.'
        );

        $this->tester->getAssertor()->assertContains('<h1>Error</h1>', $contents);
        $this->tester->getAssertor()->assertContains(
            '<a href="' . $this->config->get('baseUrl') . '">Back</a>',
            $contents
        );
    }

    /**
     * Method checkLoginPage
     *
     * @param string $contents contents
     *
     * @return void
     */
    public function checkLoginPage(string $contents): void
    {
        $this->logger->test(
            'I am going to check that I can see the Login page properly.'
        );

        $this->tester->getAssertor()->assertContains('<h1>Login</h1>', $contents);
        $this->tester->getAssertor()->assertContains(
            '<form method="POST" action="?q=">',
            $contents
        );
        $this->tester->getAssertor()->assertContains(
            '<input type="email" name="email"',
            $contents
        );
        $this->tester->getAssertor()->assertContains(
            '<input type="password" name="password"',
            $contents
        );
        $this->tester->getAssertor()->assertContains(
            '<input type="submit" value="Login"',
            $contents
        );
        $this->tester->getAssertor()->assertContains(
            '<a href="?q=registry">Registry</a>',
            $contents
        );
        $this->tester->getAssertor()->assertContains(
            '<a href="?q=pwdreset">Forgotten password</a>',
            $contents
        );
    }

    /**
     * Method checkRegistryPage
     *
     * @param string $contents contents
     *
     * @return void
     */
    public function checkRegistryPage(string $contents): void
    {
        $this->logger->test(
            'I am going to check that I can see the Registry page properly.'
        );

        $this->tester->getAssertor()->assertContains('<h1>Registry</h1>', $contents);
        $this->tester->getAssertor()->assertContains(
            '<input type="email" name="email"',
            $contents
        );
        $this->tester->getAssertor()->assertContains(
            '<input type="email" name="email_retype"',
            $contents
        );
        $this->tester->getAssertor()->assertContains(
            '<input type="password" name="password"',
            $contents
        );
        $this->tester->getAssertor()->assertContains(
            '<input type="submit" value="Register"',
            $contents
        );
        $this->tester->getAssertor()->assertContains(
            '<a href="?q=login">Login</a>',
            $contents
        );
        $this->tester->getAssertor()->assertNotContains(
            '<a href="?q=registry">Registry</a>',
            $contents
        );
        $this->tester->getAssertor()->assertNotContains(
            '<a href="?q=pwdreset">Forgotten password</a>',
            $contents
        );
    }

    /**
     * Method checkPasswordResetPage
     *
     * @param string $contents contents
     *
     * @return void
     */
    public function checkPasswordResetPage(string $contents): void
    {
        $this->logger->test(
            'I am going to check that I can see the Password Reset page properly.'
        );

        $this->tester->getAssertor()->assertContains(
            '<h1>Password reset</h1>',
            $contents
        );
        $this->tester->getAssertor()->assertContains(
            '<input type="email" name="email"',
            $contents
        );
        $this->tester->getAssertor()->assertContains(
            '<input type="submit" value="Reset password"',
            $contents
        );
    }

    /**
     * Method checkMainPage
     *
     * @param string $contents contents
     *
     * @return void
     */
    public function checkMainPage(string $contents): void
    {
        $this->logger->test(
            'I am going to check that I can see the Main page properly.'
        );

        $this->tester->getAssertor()->assertContains('<h1>Main</h1>', $contents);
        $this->tester->getAssertor()->assertContains(
            '<a href="?q=logout">Logout</a>',
            $contents
        );
    }

    /**
     * Method checkChangePasswordPage
     *
     * @param string $contents contents
     *
     * @return void
     */
    public function checkChangePasswordPage(string $contents): void
    {
        $this->logger->test(
            'I am going to check that I can see the Change Password page properly.'
        );

        $this->tester->getAssertor()->assertContains(
            '<h1>Change password</h1>',
            $contents
        );
        $this->tester->getAssertor()->assertContains(
            '<input type="password" name="password"',
            $contents
        );
        $this->tester->getAssertor()->assertContains(
            '<input type="password" name="password_retype"',
            $contents
        );
        $this->tester->getAssertor()->assertContains(
            '<input type="submit" value="Change password"',
            $contents
        );
    }
    
    /**
     * Method checkPageContainsMessage
     *
     * @param string $contents contents
     * @param string $message  message
     *
     * @return void
     */
    public function checkPageContainsMessage(
        string $contents,
        string $message
    ): void {
        $this->logger->test(
            'I am going to check that I can see the message "' . $message .
                '" on the page.'
        );

        $this->tester->getAssertor()->assertContains(
            '<div class="message">' . $message . '</div>',
            $contents
        );
    }

    /**
     * Method checkPageContainsError
     *
     * @param string $contents contents
     * @param string $error    error
     *
     * @return void
     */
    public function checkPageContainsError(string $contents, string $error): void
    {
        $this->logger->test(
            'I am going to check that I can see the error "' . $error .
                '" on the page.'
        );

        $this->tester->getAssertor()->assertContains(
            '<div class="message red">' . $error . '</div>',
            $contents
        );
    }

    /**
     * Method checkMail
     *
     * @param string $subject subject
     *
     * @return string
     */
    public function checkMail(string $subject): string
    {
        $this->logger->test(
            'I am going to check that I have got an email with subject "' .
                $subject . '".'
        );

        $files = (array)glob($this->config->get('mailerSaveMailsPath') . '/*.*');
        $this->tester->getAssertor()->assertCount(1, $files);
        $this->tester->getAssertor()->assertContains(
            AppTest::USER_EMAIL,
            (string)($files[0] ?? '')
        );
        $this->tester->getAssertor()->assertContains(
            $subject,
            (string)($files[0] ?? '')
        );
        return (string)($files[0] ?? '') ?
            (string)file_get_contents((string)($files[0] ?? '')) :
            '';
    }

    /**
     * Method checkRegistrationEmail
     *
     * @param string $email email
     *
     * @return string
     */
    public function checkRegistrationEmail(string $email): string
    {
        $this->logger->test(
            'I am going to check that the Registration email is correct.'
        );

        $this->tester->getAssertor()->assertContains(
            'Thank you for your registration,<br>',
            $email
        );
        $this->tester->getAssertor()->assertContains(
            'please activate your account by click on the following link '
                . 'or copy to your browser address line:<br>',
            $email
        );
        $this->tester->getAssertor()->assertContains(
            '<a href="' . $this->config->get('baseUrl') . '?q=activate&token=',
            $email
        );
        $this->tester->getAssertor()->assertContains(
            '">' . $this->config->get('baseUrl') . '?q=activate&token=',
            $email
        );
        $token = explode('">', explode('&token=', $email)[1] ?? '')[0] ?? '';
        $this->tester->getAssertor()->assertLongerThan(40, $token);
        return $token;
    }

    /**
     * Method checkPasswordResetEmail
     *
     * @param string $email email
     *
     * @return string
     */
    public function checkPasswordResetEmail(string $email): string
    {
        $this->logger->test(
            'I am going to check that the Password Reset email is correct.'
        );

        $this->tester->getAssertor()->assertContains(
            'You asked to reset your password,<br>',
            $email
        );
        $this->tester->getAssertor()->assertContains(
            'you can reset your password by click on the following link or copy to '.
                'your browser address line:<br>',
            $email
        );
        $this->tester->getAssertor()->assertContains(
            '<a href="' . $this->config->get('baseUrl') . '?q=newpassword&token=',
            $email
        );
        $this->tester->getAssertor()->assertContains(
            '">' . $this->config->get('baseUrl') . '?q=newpassword&token=',
            $email
        );
        $this->tester->getAssertor()->assertContains(
            'If you did not asked to reset password please ignore this message.<br>',
            $email
        );

        $token = explode(
            '">',
            explode(
                '&token=',
                $email
            )[1]
        )[0];
        $this->tester->getAssertor()->assertLongerThan(40, $token);
        return $token;
    }

    /**
     * Method getLogger
     *
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * Method getTester
     *
     * @return Tester
     */
    public function getTester(): Tester
    {
        return $this->tester;
    }

    /**
     * Method getConfig
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Method getMysql
     *
     * @return Mysql
     */
    public function getMysql(): Mysql
    {
        return $this->mysql;
    }
}
