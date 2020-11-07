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
 * AppInvalidRegTest
 *
 * @category  PHP
 * @package   GyMadarasz\Test
 * @author    Gyula Madarasz <gyula.madarasz@gmail.com>
 * @copyright 2020 Gyula Madarasz
 * @license   Copyright (c) all right reserved.
 * @link      this
 */
class AppInvalidRegTest
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
        
        $this->checkInvalidRegistryShouldFails();
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
     * Method checkEmptyRegistrationValidation
     *
     * @return void
     */
    protected function checkEmptyRegistrationValidation(): void
    {
        $this->appTest->getLogger()->test(
            'I am going to send an empty registration'
        );

        $contents = $this->appTest->getTester()->post(
            '?q=registry',
            [
            'email' => '',
            'email_retype' => '',
            'password' => '',
            ]
        );
        $this->appTest->getAppChecker()->checkRegistryPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsError(
            $contents,
            'Email can not be empty'
        );
    }
    
    /**
     * Method checkTwoEmailNotMatchValidation
     *
     * @return void
     */
    protected function checkTwoEmailNotMatchValidation(): void
    {
        $this->appTest->getLogger()->test(
            'I am going to send two different email address'
        );

        $contents = $this->appTest->getTester()->post(
            '?q=registry',
            [
            'email' => 'email@address.org',
            'email_retype' => 'email@misspelled.org',
            'password' => '',
            ]
        );
        $this->appTest->getAppChecker()->checkRegistryPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsError(
            $contents,
            'Email fields are not the same'
        );
    }
    
    /**
     * Method checkInvalidEmailValidation
     *
     * @return void
     */
    protected function checkInvalidEmailValidation(): void
    {
        $this->appTest->getLogger()->test(
            'I am going to send an invalid email address'
        );

        $contents = $this->appTest->getTester()->post(
            '?q=registry',
            [
            'email' => 'invalid-address.org',
            'email_retype' => 'invalid-address.org',
            'password' => '',
            ]
        );
        $this->appTest->getAppChecker()->checkRegistryPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsError(
            $contents,
            'Email address is invalid'
        );
    }
    
    /**
     * Method checkShortPasswordValidation
     *
     * @return void
     */
    protected function checkShortPasswordValidation(): void
    {
        $this->appTest->getLogger()->test('I am going to send a short password');

        $contents = $this->appTest->getTester()->post(
            '?q=registry',
            [
            'email' => 'valid@address.org',
            'email_retype' => 'valid@address.org',
            'password' => 'a',
            ]
        );
        $this->appTest->getAppChecker()->checkRegistryPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsError(
            $contents,
            'Your Password Must Contain At Least 8 Characters!'
        );
    }
    
    /**
     * Method checkPasswordWithoutNumber
     *
     * @return void
     */
    protected function checkPasswordWithoutNumber(): void
    {
        $this->appTest->getLogger()->test(
            'I am going to send a password without any number'
        );

        $contents = $this->appTest->getTester()->post(
            '?q=registry',
            [
            'email' => 'valid@address.org',
            'email_retype' => 'valid@address.org',
            'password' => 'asdfghjkl',
            ]
        );
        $this->appTest->getAppChecker()->checkRegistryPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsError(
            $contents,
            'Your Password Must Contain At Least 1 Number!'
        );
    }
    
    /**
     * Method checkPasswordWithoutCapitalValidation
     *
     * @return void
     */
    protected function checkPasswordWithoutCapitalValidation(): void
    {
        $this->appTest->getLogger()->test(
            'I am going to send a password without any capital letter'
        );

        $contents = $this->appTest->getTester()->post(
            '?q=registry',
            [
            'email' => 'valid@address.org',
            'email_retype' => 'valid@address.org',
            'password' => 'asdfghjkl1234',
            ]
        );
        $this->appTest->getAppChecker()->checkRegistryPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsError(
            $contents,
            'Your Password Must Contain At Least 1 Capital Letter!'
        );
    }
    
    /**
     * Method checkPasswordWithoutLowercaseValidation
     *
     * @return void
     */
    protected function checkPasswordWithoutLowercaseValidation(): void
    {
        $this->appTest->getLogger()->test(
            'I am going to send a password without any Lowercase Letter'
        );

        $contents = $this->appTest->getTester()->post(
            '?q=registry',
            [
            'email' => 'valid@address.org',
            'email_retype' => 'valid@address.org',
            'password' => 'QWERTYU1234',
            ]
        );
        $this->appTest->getAppChecker()->checkRegistryPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsError(
            $contents,
            'Your Password Must Contain At Least 1 Lowercase Letter!'
        );
    }
    
    /**
     * Method checkUserAlreadyRediteredValidation
     *
     * @return void
     */
    protected function checkUserAlreadyRediteredValidation(): void
    {
        $this->appTest->getLogger()->test(
            'I am going to try to registrate an already exist user'
        );

        $contents = $this->appTest->getTester()->post(
            '?q=registry',
            [
            'email' => AppTest::USER_EMAIL,
            'email_retype' => AppTest::USER_EMAIL,
            'password' => 'ValidPassword123!',
            ]
        );
        $this->appTest->getAppChecker()->checkRegistryPage($contents);
        $this->appTest->getAppChecker()->checkPageContainsError(
            $contents,
            'Registration failed'
        );
    }
}
