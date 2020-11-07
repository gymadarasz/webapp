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
    protected AppChecker $appChecker;
    
    /**
     * Method __construct
     *
     * @param Tester $tester tester
     * @param Config $config config
     * @param Logger $logger logger
     * @param Mysql  $mysql  mysql
     */
    public function __construct(
        Tester $tester,
        Config $config,
        Logger $logger,
        Mysql $mysql
    ) {
        $this->tester = $tester;
        $this->config = $config;
        $this->logger = $logger;
        $this->mysql = $mysql;
    }

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
     * @param AppRegistryTest $regTest regTest
     *
     * @return void
     */
    public function test(
        Tester $tester,
        AppChecker $appChecker,
        AppRegistryTest $regTest,
        AppPasswordResetTest $pwdResetTest,
        AppErrorPageTest $errPageTest,
        AppInvalidRegTest $invalidRegTest,
        AppInvalidPwdResetTest $invalidPwdResetTest
    ): void {
        $this->tester = $tester;
        $this->appChecker = $appChecker;
        $this->appChecker->setAppTest($this);
        
        // ----- clean up -----
        $this->cleanup();

        // ------- tests ----------
        $regTest->testWith($this);
        $pwdResetTest->testWith($this);
        $errPageTest->testWith($this);
        $invalidRegTest->testWith($this);
        $invalidPwdResetTest->testWith($this);

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
        $this->appChecker->checkPageContainsMessage($contents, 'Login success');
        return $contents;
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
    
    /**
     * Method getAppChecker
     *
     * @return AppChecker
     */
    public function getAppChecker(): AppChecker
    {
        return $this->appChecker;
    }
}
