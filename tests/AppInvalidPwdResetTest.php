<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace GyMadarasz\Test;

/**
 * Description of AppInvalidPwdResetTest
 *
 * @author gyula
 */
class AppInvalidPwdResetTest
{
    protected AppTest $appTest;
    
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
