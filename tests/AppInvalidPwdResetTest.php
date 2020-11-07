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
 * AppInvalidPwdResetTest
 *
 * @category  PHP
 * @package   GyMadarasz\Test
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class AppInvalidPwdResetTest
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
        
        $this->checkInvalidPasswordResetShouldFails();
    }

    /**
     * Method checkInvalidPasswordResetShouldFails
     *
     * @return void
     */
    protected function checkInvalidPasswordResetShouldFails(): void
    {
        $this->appTest->getLogger()->test(
            'I am going to try to send an empty email to password reset.'
        );

        $contents = $this->appTest->getTester()->post(
            '?q=pwdreset',
            [
            'email' => '',
            ]
        );
        $this->appTest->getAppChecker()->checkLoginPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsError(
            $contents,
            'Request for password reset is failed'
        );


        $this->appTest->getLogger()->test(
            'I am going to try to send an invalid email to password reset.'
        );

        $contents = $this->appTest->getTester()->post(
            '?q=pwdreset',
            [
            'email' => 'invalid-email-address',
            ]
        );
        $this->appTest->getAppChecker()->checkLoginPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsError(
            $contents,
            'Request for password reset is failed'
        );
    }
}
