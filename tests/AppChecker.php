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
 * AppChecker
 *
 * @category  PHP
 * @package   GyMadarasz\Test
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class AppChecker
{
    protected AppTest $appTest;
    
    /**
     * Method setAppTest
     *
     * @param \GyMadarasz\Test\AppTest $appTest appTest
     *
     * @return void
     */
    public function setAppTest(AppTest $appTest): void
    {
        $this->appTest = $appTest;
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
        $this->appTest->getLogger()->test(
            'I am going to check that I can see the Error page properly.'
        );

        $this->appTest->getTester()->getAssertor()->assertContains(
            '<h1>Error</h1>',
            $contents
        );
        $this->appTest->getTester()->getAssertor()->assertContains(
            '<a href="' . $this->appTest->getConfig()->get('baseUrl') . '">Back</a>',
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
        $this->appTest->getLogger()->test(
            'I am going to check that I can see the Login page properly.'
        );

        $this->appTest->getTester()->getAssertor()->assertContains(
            '<h1>Login</h1>',
            $contents
        );
        $this->appTest->getTester()->getAssertor()->assertContains(
            '<form method="POST" action="?q=">',
            $contents
        );
        $this->appTest->getTester()->getAssertor()->assertContains(
            '<input type="email" name="email"',
            $contents
        );
        $this->appTest->getTester()->getAssertor()->assertContains(
            '<input type="password" name="password"',
            $contents
        );
        $this->appTest->getTester()->getAssertor()->assertContains(
            '<input type="submit" value="Login"',
            $contents
        );
        $this->appTest->getTester()->getAssertor()->assertContains(
            '<a href="?q=registry">Registry</a>',
            $contents
        );
        $this->appTest->getTester()->getAssertor()->assertContains(
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
        $this->appTest->getLogger()->test(
            'I am going to check that I can see the Registry page properly.'
        );

        $this->appTest->getTester()->getAssertor()->assertContains(
            '<h1>Registry</h1>',
            $contents
        );
        $this->appTest->getTester()->getAssertor()->assertContains(
            '<input type="email" name="email"',
            $contents
        );
        $this->appTest->getTester()->getAssertor()->assertContains(
            '<input type="email" name="email_retype"',
            $contents
        );
        $this->appTest->getTester()->getAssertor()->assertContains(
            '<input type="password" name="password"',
            $contents
        );
        $this->appTest->getTester()->getAssertor()->assertContains(
            '<input type="submit" value="Register"',
            $contents
        );
        $this->appTest->getTester()->getAssertor()->assertContains(
            '<a href="?q=login">Login</a>',
            $contents
        );
        $this->appTest->getTester()->getAssertor()->assertNotContains(
            '<a href="?q=registry">Registry</a>',
            $contents
        );
        $this->appTest->getTester()->getAssertor()->assertNotContains(
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
        $this->appTest->getLogger()->test(
            'I am going to check that I can see the Password Reset page properly.'
        );

        $this->appTest->getTester()->getAssertor()->assertContains(
            '<h1>Password reset</h1>',
            $contents
        );
        $this->appTest->getTester()->getAssertor()->assertContains(
            '<input type="email" name="email"',
            $contents
        );
        $this->appTest->getTester()->getAssertor()->assertContains(
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
        $this->appTest->getLogger()->test(
            'I am going to check that I can see the Main page properly.'
        );

        $this->appTest->getTester()->getAssertor()->assertContains(
            '<h1>Main</h1>',
            $contents
        );
        $this->appTest->getTester()->getAssertor()->assertContains(
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
        $this->appTest->getLogger()->test(
            'I am going to check that I can see the Change Password page properly.'
        );

        $this->appTest->getTester()->getAssertor()->assertContains(
            '<h1>Change password</h1>',
            $contents
        );
        $this->appTest->getTester()->getAssertor()->assertContains(
            '<input type="password" name="password"',
            $contents
        );
        $this->appTest->getTester()->getAssertor()->assertContains(
            '<input type="password" name="password_retype"',
            $contents
        );
        $this->appTest->getTester()->getAssertor()->assertContains(
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
        $this->appTest->getLogger()->test(
            'I am going to check that I can see the message "' . $message .
                '" on the page.'
        );

        $this->appTest->getTester()->getAssertor()->assertContains(
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
        $this->appTest->getLogger()->test(
            'I am going to check that I can see the error "' . $error .
                '" on the page.'
        );

        $this->appTest->getTester()->getAssertor()->assertContains(
            '<div class="message red">' . $error . '</div>',
            $contents
        );
    }
}
