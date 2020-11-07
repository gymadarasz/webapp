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
 * AppErrorPageTest
 *
 * @category  PHP
 * @package   GyMadarasz\Test
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class AppErrorPageTest
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
        
        $this->checkErrorPageWorks();
        $this->checkLoginAfterLoginShouldFails();
    }
    
    /**
     * Method checkErrorPageWorks
     *
     * @return void
     */
    protected function checkErrorPageWorks(): void
    {
        $this->appTest->getLogger()->test(
            'I am going to login with my new password'
        );
        $contents = $this->appTest->checkIfICanLogin();
        $this->appTest->getAppChecker()->checkMainPage($contents);

        $this->appTest->getLogger()->test(
            'I am going to the restricted Index page.'
        );

        $contents = $this->appTest->getTester()->get('');
        $this->appTest->getAppChecker()->checkMainPage($contents);


        $this->appTest->getLogger()->test(
            'I am going to a non-exists page '
                . 'while I am logged in to see it shows the error page.'
        );

        $contents = $this->appTest->getTester()->get(
            '?q=this-page-should-not-exists'
        );
        $this->appTest->getAppChecker()->checkErrorPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsError(
            $contents,
            'Request is not supported.'
        );
        

        $this->appTest->getLogger()->test('I am going to Logout.');

        $contents = $this->appTest->getTester()->get('?q=logout');
        $this->appTest->getAppChecker()->checkLoginPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsMessage(
            $contents,
            'Logout success'
        );


        $this->appTest->getLogger()->test(
            'I am going to a non-exists page '
                . 'while I am logged out to see it shows the error page.'
        );

        $contents = $this->appTest->getTester()->get(
            '?q=this-page-should-not-exists'
        );
        $this->appTest->getAppChecker()->checkErrorPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsError(
            $contents,
            'Request is not supported.'
        );
    }

    /**
     * Method checkLoginAfterLoginShouldFails
     *
     * @return void
     */
    protected function checkLoginAfterLoginShouldFails(): void
    {
        $this->appTest->getLogger()->test('I am going to login');
        $contents = $this->appTest->checkIfICanLogin();
        $this->appTest->getAppChecker()->checkMainPage($contents);


        $this->appTest->getLogger()->test(
            'I am going to try login again '
                . 'when I am logged in so that I get an error page.'
        );

        $contents = $this->appTest->getTester()->post(
            '',
            [
            'email' => AppTest::USER_EMAIL,
            'password' => AppTest::USER_PASSWORD,
            ]
        );
        $this->appTest->getAppChecker()->checkErrorPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsError(
            $contents,
            'Request is not supported.'
        );


        $this->appTest->getLogger()->test('I am going to Logout.');

        $contents = $this->appTest->getTester()->get('?q=logout');
        $this->appTest->getAppChecker()->checkLoginPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsMessage(
            $contents,
            'Logout success'
        );
    }
}
