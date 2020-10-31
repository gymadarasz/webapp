<?php declare(strict_types = 1);

namespace Madsoft\Test;

use GyMadarasz\WebApp\Service\Config;
use GyMadarasz\WebApp\Service\Logger;
use GyMadarasz\WebApp\Service\Mysql;
use Madsoft\Test\Test;

class AppTest implements Test
{
    const USER_EMAIL = 'tester@example.com';
    const USER_PASSWORD_OLD = 'OldPassword123!';
    const USER_PASSWORD = 'Pass1234';

    private Config $config;
    private Logger $logger;
    private Mysql $mysql;

    public function __construct(Config $config, Logger $logger, Mysql $mysql)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->mysql = $mysql;
    }

    private function cleanupUsers(): void
    {
        $this->logger->debug('I am going to delete all users from database.');

        $this->mysql->connect();
        $this->mysql->query("TRUNCATE user");
    }

    private function cleanupMails(): void
    {
        $this->logger->debug('I am going to delete all saved emails.');

        $files = (array)glob($this->config->get('mailerSaveMailsPath') . '/*.*');
        foreach ($files as $file) {
            unlink((string)$file);
        }
    }

    private function cleanup(): void
    {
        $this->cleanupUsers();
        $this->cleanupMails();
    }

    public function run(Tester $tester): void
    {
        // ----- clean up -----
        $this->cleanup();

        // ------- tests ----------
        $this->checkRegistryLoginAndPasswordResetProcess($tester);
        $this->checkLoginAfterLoginShouldFails($tester);
        $this->checkInvalidRegistryShouldFails($tester);
        $this->checkInvalidPasswordResetShouldFails($tester);

        // ----- clean up -----
        //$this->cleanup();
    }

    private function checkRegistryLoginAndPasswordResetProcess(Tester $tester): void
    {
        $this->logger->debug('I am going to the Login page.');

        $contents = $tester->get('');
        $this->checkLoginPage($tester, $contents);


        $this->logger->debug('I am posting my account, but it should fails because I am not registered.');

        $contents = $tester->post('', [
            'email' => AppTest::USER_EMAIL,
            'password' => AppTest::USER_PASSWORD_OLD,
        ]);
        $this->checkLoginPage($tester, $contents);
        $this->checkPageContainsError($tester, $contents, 'Login failed');


        $this->logger->debug('I am going to Register page.');

        $contents = $tester->get('?q=registry');
        $this->checkRegistryPage($tester, $contents);


        $this->logger->debug('I am posting my registry details to the Register page.');

        $contents = $tester->post('?q=registry', [
            'email' => AppTest::USER_EMAIL,
            'email_retype' => AppTest::USER_EMAIL,
            'password' => AppTest::USER_PASSWORD_OLD,
        ]);
        $this->checkLoginPage($tester, $contents);
        $this->checkPageContainsMessage($tester, $contents, 'Registration success, please check your email inbox and validate your account, or try to resend by <a href="?q=resend">click here</a>');


        $this->logger->debug('I am going to check my activation email.');

        $email = $this->checkMail($tester, 'Activate your account');
        $token = $this->checkRegistrationEmail($tester, $email);
        $this->cleanupMails();


        $this->logger->debug('I am going to click on resend link.');

        $contents = $tester->get('?q=resend');
        $this->checkLoginPage($tester, $contents);
        $this->checkPageContainsMessage($tester, $contents, 'Attempt to resend activation email, please check your email inbox and validate your account, or try to resend by <a href="?q=resend">click here</a>');


        $this->logger->debug('I am posting my account details to Login page, but it should fails because I am not activated my newly registered account.');

        $contents = $tester->post('', [
            'email' => AppTest::USER_EMAIL,
            'password' => AppTest::USER_PASSWORD_OLD,
        ]);
        $this->checkLoginPage($tester, $contents);
        $this->checkPageContainsError($tester, $contents, 'Login failed');


        $this->logger->debug('I am going to check my activation email.');

        $email = $this->checkMail($tester, 'Activate your account');
        $token = $this->checkRegistrationEmail($tester, $email);
        $this->cleanupMails();


        $this->logger->debug('I am going to try to activate my account with an incorrect activation token.');

        $contents = $tester->get($this->config->get('baseUrl') . '?q=activate&token=incorrect');
        $this->checkErrorPage($tester, $contents);
        $this->checkPageContainsError($tester, $contents, 'Activation token is incorrect.');


        $this->logger->debug('I am going to activate my account with the correct activation token.');

        $contents = $tester->get($this->config->get('baseUrl') . '?q=activate&token=' . $token);
        $this->checkLoginPage($tester, $contents);
        $this->checkPageContainsMessage($tester, $contents, 'Your account is now activated.');


        $this->logger->debug('I am just check if I am activated in the database properly?');
        
        $tester->assertEquals(
            1,
            (int)((array)$this->mysql->selectOne(
                "SELECT active FROM user WHERE email = '" . AppTest::USER_EMAIL . "' LIMIT 1;"
            ))['active']
        );


        $this->logger->debug('I am going to post my newly registered account details.');

        $contents = $tester->post('', [
            'email' => AppTest::USER_EMAIL,
            'password' => AppTest::USER_PASSWORD_OLD,
        ]);
        $this->checkIndexPage($tester, $contents);
        $this->checkPageContainsMessage($tester, $contents, 'Login success');
        

        $this->logger->debug('I am going to the restricted Index page.');

        $contents = $tester->get('');
        $this->checkIndexPage($tester, $contents);


        $this->logger->debug('I am going to Logout.');

        $contents = $tester->get('?q=logout');
        $this->checkLoginPage($tester, $contents);
        $this->checkPageContainsMessage($tester, $contents, 'Logout success');


        $this->logger->debug('I am going to reset my password.');

        $contents = $tester->get('?q=pwdreset');
        $this->checkPasswordResetPage($tester, $contents);


        $this->logger->debug('I am going to post my email address to Password Reset page.');

        $contents = $tester->post('?q=pwdreset', [
            'email' => AppTest::USER_EMAIL,
        ]);
        $this->checkLoginPage($tester, $contents);
        $this->checkPageContainsMessage($tester, $contents, 'We sent an email to your inbox, please follow the given instructions to change your password');


        $this->logger->debug('I am going to check my emails for password reset instructions.');
        
        $email = $this->checkMail($tester, 'Password reset request');
        $token = $this->checkPasswordResetEmail($tester, $email);
        $this->cleanupMails();


        $this->logger->debug('I am going to check the Password Reset page with an incorrect token.');

        $contents = $tester->get($this->config->get('baseUrl') . '?q=newpassword&token=invalid');
        $this->checkChangePasswordPage($tester, $contents);
        $this->checkPageContainsError($tester, $contents, 'Invalid token');


        $this->logger->debug('I am going to follow the Password Reset link to the Password Reset page.');

        $contents = $tester->get($this->config->get('baseUrl') . '?q=newpassword&token=' . $token);
        $this->checkChangePasswordPage($tester, $contents);


        $this->logger->debug('I am going to post my new password to reset my password.');

        $contents = $tester->post($this->config->get('baseUrl') . '?q=newpassword&token=' . $token, [
            'password' => AppTest::USER_PASSWORD,
            'password_retype' => AppTest::USER_PASSWORD,
        ]);
        $this->checkLoginPage($tester, $contents);
        $this->checkPageContainsMessage($tester, $contents, 'Your password changed, please log in');


        $this->logger->debug('I am going to login with my new password');

        $contents = $tester->post('', [
            'email' => AppTest::USER_EMAIL,
            'password' => AppTest::USER_PASSWORD,
        ]);
        $this->checkIndexPage($tester, $contents);
        $this->checkPageContainsMessage($tester, $contents, 'Login success');


        $this->logger->debug('I am going to the restricted Index page.');

        $contents = $tester->get('');
        $this->checkIndexPage($tester, $contents);


        $this->logger->debug('I am going to a non-exists page while I am logged in to see it shows the error page.');

        $contents = $tester->get('?q=this-page-should-not-exists');
        $this->checkErrorPage($tester, $contents);
        $this->checkPageContainsError($tester, $contents, 'Request is not supported.');
        

        $this->logger->debug('I am going to Logout.');

        $contents = $tester->get('?q=logout');
        $this->checkLoginPage($tester, $contents);
        $this->checkPageContainsMessage($tester, $contents, 'Logout success');


        $this->logger->debug('I am going to a non-exists page while I am logged out to see it shows the error page.');

        $contents = $tester->get('?q=this-page-should-not-exists');
        $this->checkErrorPage($tester, $contents);
        $this->checkPageContainsError($tester, $contents, 'Request is not supported.');
    }

    private function checkLoginAfterLoginShouldFails(Tester $tester): void
    {

        $this->logger->debug('I am going to login');

        $contents = $tester->post('', [
            'email' => AppTest::USER_EMAIL,
            'password' => AppTest::USER_PASSWORD,
        ]);
        $this->checkIndexPage($tester, $contents);
        $this->checkPageContainsMessage($tester, $contents, 'Login success');


        $this->logger->debug('I am going to try login again when I am logged in so that I get an error page.');

        $contents = $tester->post('', [
            'email' => AppTest::USER_EMAIL,
            'password' => AppTest::USER_PASSWORD,
        ]);
        $this->checkErrorPage($tester, $contents);
        $this->checkPageContainsError($tester, $contents, 'Request is not supported.');


        $this->logger->debug('I am going to Logout.');

        $contents = $tester->get('?q=logout');
        $this->checkLoginPage($tester, $contents);
        $this->checkPageContainsMessage($tester, $contents, 'Logout success');
    }

    private function checkInvalidRegistryShouldFails(Tester $tester): void {

        $this->logger->debug('I am going to send an empty registration');

        $contents = $tester->post('?q=registry', [
            'email' => '',
            'email_retype' => '',
            'password' => '',
        ]);
        $this->checkRegistryPage($tester, $contents);
        $this->checkPageContainsError($tester, $contents, 'Email can not be empty');


        $this->logger->debug('I am going to send two different email address');

        $contents = $tester->post('?q=registry', [
            'email' => 'email@address.org',
            'email_retype' => 'email@misspelled.org',
            'password' => '',
        ]);
        $this->checkRegistryPage($tester, $contents);
        $this->checkPageContainsError($tester, $contents, 'Email fields are not the same');


        $this->logger->debug('I am going to send an invalid email address');

        $contents = $tester->post('?q=registry', [
            'email' => 'invalid-address.org',
            'email_retype' => 'invalid-address.org',
            'password' => '',
        ]);
        $this->checkRegistryPage($tester, $contents);
        $this->checkPageContainsError($tester, $contents, 'Email address is invalid');


        $this->logger->debug('I am going to send a short password');

        $contents = $tester->post('?q=registry', [
            'email' => 'valid@address.org',
            'email_retype' => 'valid@address.org',
            'password' => 'a',
        ]);
        $this->checkRegistryPage($tester, $contents);
        $this->checkPageContainsError($tester, $contents, 'Your Password Must Contain At Least 8 Characters!');
    

        $this->logger->debug('I am going to send a password without any number');

        $contents = $tester->post('?q=registry', [
            'email' => 'valid@address.org',
            'email_retype' => 'valid@address.org',
            'password' => 'asdfghjkl',
        ]);
        $this->checkRegistryPage($tester, $contents);
        $this->checkPageContainsError($tester, $contents, 'Your Password Must Contain At Least 1 Number!');
      

        $this->logger->debug('I am going to send a password without any capital letter');

        $contents = $tester->post('?q=registry', [
            'email' => 'valid@address.org',
            'email_retype' => 'valid@address.org',
            'password' => 'asdfghjkl1234',
        ]);
        $this->checkRegistryPage($tester, $contents);
        $this->checkPageContainsError($tester, $contents, 'Your Password Must Contain At Least 1 Capital Letter!');
      

        $this->logger->debug('I am going to send a password without any Lowercase Letter');

        $contents = $tester->post('?q=registry', [
            'email' => 'valid@address.org',
            'email_retype' => 'valid@address.org',
            'password' => 'QWERTYU1234',
        ]);
        $this->checkRegistryPage($tester, $contents);
        $this->checkPageContainsError($tester, $contents, 'Your Password Must Contain At Least 1 Lowercase Letter!');


        $this->logger->debug('I am going to try to registrate an already exist user');

        $contents = $tester->post('?q=registry', [
            'email' => AppTest::USER_EMAIL,
            'email_retype' => AppTest::USER_EMAIL,
            'password' => 'ValidPassword123!',
        ]);
        $this->checkRegistryPage($tester, $contents);
        $this->checkPageContainsError($tester, $contents, 'Registration failed');        
    }

    private function checkInvalidPasswordResetShouldFails(Tester $tester): void
    {

        $this->logger->debug('I am going to try to send an empty email to password reset.');

        $contents = $tester->post('?q=pwdreset', [
            'email' => '',
        ]);
        $this->checkLoginPage($tester, $contents);
        $this->checkPageContainsError($tester, $contents, 'Request for password reset is failed'); 


        $this->logger->debug('I am going to try to send an invalid email to password reset.');

        $contents = $tester->post('?q=pwdreset', [
            'email' => 'invalid-email-address',
        ]);
        $this->checkLoginPage($tester, $contents);
        $this->checkPageContainsError($tester, $contents, 'Request for password reset is failed'); 
    }

    private function checkErrorPage(Tester $tester, string $contents): void
    {
        $this->logger->debug('I am check that I can see the Error page properly.');

        $tester->assertContains('<h1>Error</h1>', $contents);
        $tester->assertContains('<a href="' . $this->config->get('baseUrl') . '">Back</a>', $contents);
    }

    private function checkLoginPage(Tester $tester, string $contents): void
    {
        $this->logger->debug('I am check that I can see the Login page properly.');

        $tester->assertContains('<h1>Login</h1>', $contents);
        $tester->assertContains('<form method="POST" action="?q=">', $contents);
        $tester->assertContains('<input type="email" name="email"', $contents);
        $tester->assertContains('<input type="password" name="password"', $contents);
        $tester->assertContains('<input type="submit" value="Login"', $contents);
        $tester->assertContains('<a href="?q=registry">Registry</a>', $contents);
        $tester->assertContains('<a href="?q=pwdreset">Forgotten password</a>', $contents);
    }

    private function checkRegistryPage(Tester $tester, string $contents): void
    {
        $this->logger->debug('I am check that I can see the Registry page properly.');

        $tester->assertContains('<h1>Registry</h1>', $contents);
        $tester->assertContains('<input type="email" name="email"', $contents);
        $tester->assertContains('<input type="email" name="email_retype"', $contents);
        $tester->assertContains('<input type="password" name="password"', $contents);
        $tester->assertContains('<input type="submit" value="Register"', $contents);
        $tester->assertContains('<a href="?q=login">Login</a>', $contents);
        $tester->assertNotContains('<a href="?q=registry">Registry</a>', $contents);
        $tester->assertNotContains('<a href="?q=pwdreset">Forgotten password</a>', $contents);
    }

    private function checkPasswordResetPage(Tester $tester, string $contents): void
    {
        $this->logger->debug('I am check that I can see the Password Reset page properly.');

        $tester->assertContains('<h1>Password reset</h1>', $contents);
        $tester->assertContains('<input type="email" name="email"', $contents);
        $tester->assertContains('<input type="submit" value="Reset password"', $contents);
    }

    private function checkIndexPage(Tester $tester, string $contents): void
    {
        $this->logger->debug('I am check that I can see the Index page properly.');

        $tester->assertContains('<h1>Index</h1>', $contents);
        $tester->assertContains('<a href="?q=logout">Logout</a>', $contents);
    }

    private function checkChangePasswordPage(Tester $tester, string $contents): void
    {
        $this->logger->debug('I am check that I can see the Change Password page properly.');

        $tester->assertContains('<h1>Change password</h1>', $contents);
        $tester->assertContains('<input type="password" name="password"', $contents);
        $tester->assertContains('<input type="password" name="password_retype"', $contents);
        $tester->assertContains('<input type="submit" value="Change password"', $contents);
    }
    
    private function checkPageContainsMessage(Tester $tester, string $contents, string $message): void
    {
        $this->logger->debug('I am check that I can see the message "' . $message . '" on the page.');

        $tester->assertContains('<div class="message">' . $message . '</div>', $contents);
    }

    private function checkPageContainsError(Tester $tester, string $contents, string $error): void
    {
        $this->logger->debug('I am check that I can see the error "' . $error . '" on the page.');

        $tester->assertContains('<div class="message red">' . $error . '</div>', $contents);
    }

    private function checkMail(Tester $tester, string $subject): string
    {
        $this->logger->debug('I am check that I have got an email with subject "' . $subject . '".');

        $files = (array)glob($this->config->get('mailerSaveMailsPath') . '/*.*');
        $tester->assertCount(1, $files);
        $tester->assertContains(AppTest::USER_EMAIL, (string)$files[0]);
        $tester->assertContains($subject, (string)$files[0]);
        return (string)file_get_contents((string)$files[0]);
    }

    private function checkRegistrationEmail(Tester $tester, string $email): string
    {
        $this->logger->debug('I am going to check that the Registration email is correct.');

        $tester->assertContains('Thank you for your registration,<br>', $email);
        $tester->assertContains('please activate your account by click on the following link or copy to your browser address line:<br>', $email);
        $tester->assertContains('<a href="' . $this->config->get('baseUrl') . '?q=activate&token=', $email);
        $tester->assertContains('">' . $this->config->get('baseUrl') . '?q=activate&token=', $email);
        $token = explode('">', explode('&token=', $email)[1])[0];
        $tester->assertLongerThan(40, $token);
        return $token;
    }

    private function checkPasswordResetEmail(Tester $tester, string $email): string
    {
        $this->logger->debug('I am going to check that the Password Reset email is correct.');

        $tester->assertContains('You asked to reset your password,<br>', $email);
        $tester->assertContains('you can reset your password by click on the following link or copy to your browser address line:<br>', $email);
        $tester->assertContains('<a href="' . $this->config->get('baseUrl') . '?q=newpassword&token=', $email);
        $tester->assertContains('">' . $this->config->get('baseUrl') . '?q=newpassword&token=', $email);
        $tester->assertContains('If you did not asked to reset password please ignore this message.<br>', $email);

        $token = explode('">', explode('&token=', $email)[1])[0];
        $tester->assertLongerThan(40, $token);
        return $token;
    }
}
