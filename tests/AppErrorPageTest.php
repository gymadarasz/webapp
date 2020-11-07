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
    
    public function testWith(AppTest $appTest): void
    {
        $this->appTest = $appTest;
        
        $this->checkErrorPageWorks();
    }
    
    protected function checkErrorPageWorks(): void
    {
        $this->appTest->getLogger()->test('I am going to login with my new password');
        $contents = $this->appTest->checkIfICanLogin();
        $this->appTest->checkMainPage($contents);

        $this->appTest->getLogger()->test('I am going to the restricted Index page.');

        $contents = $this->appTest->getTester()->get('');
        $this->appTest->checkMainPage($contents);


        $this->appTest->getLogger()->test(
            'I am going to a non-exists page '
                . 'while I am logged in to see it shows the error page.'
        );

        $contents = $this->appTest->getTester()->get('?q=this-page-should-not-exists');
        $this->appTest->checkErrorPage($contents);
        $this->appTest->checkPageContainsError($contents, 'Request is not supported.');
        

        $this->appTest->getLogger()->test('I am going to Logout.');

        $contents = $this->appTest->getTester()->get('?q=logout');
        $this->appTest->checkLoginPage($contents);
        $this->appTest->checkPageContainsMessage($contents, 'Logout success');


        $this->appTest->getLogger()->test(
            'I am going to a non-exists page '
                . 'while I am logged out to see it shows the error page.'
        );

        $contents = $this->appTest->getTester()->get('?q=this-page-should-not-exists');
        $this->appTest->checkErrorPage($contents);
        $this->appTest->checkPageContainsError($contents, 'Request is not supported.');
    }
}
