<?php declare(strict_types = 1);

namespace GyMadarasz\Test;

use GyMadarasz\WebApp\Service\Config;
use GyMadarasz\WebApp\Service\Logger;
use GyMadarasz\WebApp\Service\Mysql;

class AppTest
{
    const USER_EMAIL = 'tester@example.com';
    const USER_PASSWORD_OLD = 'OldPassword123!';
    const USER_PASSWORD = 'Pass1234';

    protected Tester $tester;
    protected Config $config;
    protected Logger $logger;
    protected Mysql $mysql;

    public function __construct(Tester $tester, Config $config, Logger $logger, Mysql $mysql)
    {
        $this->tester = $tester;
        $this->config = $config;
        $this->logger = $logger;
        $this->mysql = $mysql;
    }

    protected function cleanupUsers(): void
    {
        $this->logger->test('I am going to delete all users from database.');

        $this->mysql->query("DELETE FROM user WHERE email = '" . AppTest::USER_EMAIL . "';");
    }

    protected function cleanupMails(): void
    {
        $this->logger->test('I am going to delete all saved emails.');

        $files = (array)glob($this->config->get('mailerSaveMailsPath') . '/*.*');
        foreach ($files as $file) {
            unlink((string)$file);
        }
    }

    protected function cleanup(): void
    {
        $this->cleanupUsers();
        $this->cleanupMails();
    }

    public function test(): void
    {

        // ----- clean up -----
        $this->cleanup();

        // ------- tests ----------
        $this->checkRegistryLoginAndPasswordResetProcess();
        $this->checkLoginAfterLoginShouldFails();
        $this->checkInvalidRegistryShouldFails();
        $this->checkInvalidPasswordResetShouldFails();

        // ----- clean up -----
        //$this->cleanup();
    }

    protected function checkRegistryLoginAndPasswordResetProcess(): void
    {
        $this->logger->test('I am going to the Login page.');

        $contents = $this->tester->get('');
        $this->checkLoginPage($contents);


        $this->logger->test('I am posting my account, but it should fails because I am not registered.');

        $contents = $this->tester->post('', [
            'email' => AppTest::USER_EMAIL,
            'password' => AppTest::USER_PASSWORD_OLD,
        ]);
        $this->checkLoginPage($contents);
        $this->checkPageContainsError($contents, 'Login failed');


        $this->logger->test('I am going to Register page.');

        $contents = $this->tester->get('?q=registry');
        $this->checkRegistryPage($contents);


        $this->logger->test('I am posting my registry details to the Register page.');

        $contents = $this->tester->post('?q=registry', [
            'email' => AppTest::USER_EMAIL,
            'email_retype' => AppTest::USER_EMAIL,
            'password' => AppTest::USER_PASSWORD_OLD,
        ]);
        $this->checkLoginPage($contents);
        $this->checkPageContainsMessage($contents, 'Registration success, please check your email inbox and validate your account, or try to resend by <a href="?q=resend">click here</a>');


        $this->logger->test('I am going to check my activation email.');

        $email = $this->checkMail('Activate your account');
        $token = $this->checkRegistrationEmail($email);
        $this->cleanupMails();


        $this->logger->test('I am going to click on resend link.');

        $contents = $this->tester->get('?q=resend');
        $this->checkLoginPage($contents);
        $this->checkPageContainsMessage($contents, 'Attempt to resend activation email, please check your email inbox and validate your account, or try to resend by <a href="?q=resend">click here</a>');


        $this->logger->test('I am posting my account details to Login page, but it should fails because I am not activated my newly registered account.');

        $contents = $this->tester->post('', [
            'email' => AppTest::USER_EMAIL,
            'password' => AppTest::USER_PASSWORD_OLD,
        ]);
        $this->checkLoginPage($contents);
        $this->checkPageContainsError($contents, 'Login failed');


        $this->logger->test('I am going to check my activation email.');

        $email = $this->checkMail('Activate your account');
        $token = $this->checkRegistrationEmail($email);
        $this->cleanupMails();


        $this->logger->test('I am going to try to activate my account with an incorrect activation token.');

        $contents = $this->tester->get($this->config->get('baseUrl') . '?q=activate&token=incorrect');
        $this->checkErrorPage($contents);
        $this->checkPageContainsError($contents, 'Activation token is incorrect.');


        $this->logger->test('I am going to activate my account with the correct activation token.');

        $contents = $this->tester->get($this->config->get('baseUrl') . '?q=activate&token=' . $token);
        $this->checkLoginPage($contents);
        $this->checkPageContainsMessage($contents, 'Your account is now activated.');


        $this->logger->test('I am just check if I am activated in the database properly?');
        $results = $this->mysql->selectOne(
            "SELECT active FROM user WHERE email = '" . AppTest::USER_EMAIL . "' LIMIT 1;"
        );
        $this->tester->assertTrue(isset($results['active']));
        $this->tester->assertEquals(
            1,
            (int)((array)$results)['active']
        );


        $this->logger->test('I am going to post my newly registered account details.');
        $contents = $this->checkIfICanLogin(AppTest::USER_EMAIL, AppTest::USER_PASSWORD_OLD);
        $this->checkMainPage($contents);
        

        $this->logger->test('I am going to the restricted Index page.');

        $contents = $this->tester->get('');
        $this->checkMainPage($contents);


        $this->logger->test('I am going to Logout.');

        $contents = $this->tester->get('?q=logout');
        $this->checkLoginPage($contents);
        $this->checkPageContainsMessage($contents, 'Logout success');


        $this->logger->test('I am going to reset my password.');

        $contents = $this->tester->get('?q=pwdreset');
        $this->checkPasswordResetPage($contents);


        $this->logger->test('I am going to post my email address to Password Reset page.');

        $contents = $this->tester->post('?q=pwdreset', [
            'email' => AppTest::USER_EMAIL,
        ]);
        $this->checkLoginPage($contents);
        $this->checkPageContainsMessage($contents, 'We sent an email to your inbox, please follow the given instructions to change your password');


        $this->logger->test('I am going to check my emails for password reset instructions.');
        
        $email = $this->checkMail('Password reset request');
        $token = $this->checkPasswordResetEmail($email);
        $this->cleanupMails();


        $this->logger->test('I am going to check the Password Reset page with an incorrect token.');

        $contents = $this->tester->get($this->config->get('baseUrl') . '?q=newpassword&token=invalid');
        $this->checkChangePasswordPage($contents);
        $this->checkPageContainsError($contents, 'Invalid token');


        $this->logger->test('I am going to follow the Password Reset link to the Password Reset page.');

        $contents = $this->tester->get($this->config->get('baseUrl') . '?q=newpassword&token=' . $token);
        $this->checkChangePasswordPage($contents);


        $this->logger->test('I am going to post my new password to reset my password.');

        $contents = $this->tester->post($this->config->get('baseUrl') . '?q=newpassword&token=' . $token, [
            'password' => AppTest::USER_PASSWORD,
            'password_retype' => AppTest::USER_PASSWORD,
        ]);
        $this->checkLoginPage($contents);
        $this->checkPageContainsMessage($contents, 'Your password changed, please log in');


        $this->logger->test('I am going to login with my new password');
        $contents = $this->checkIfICanLogin();
        $this->checkMainPage($contents);

        $this->logger->test('I am going to the restricted Index page.');

        $contents = $this->tester->get('');
        $this->checkMainPage($contents);


        $this->logger->test('I am going to a non-exists page while I am logged in to see it shows the error page.');

        $contents = $this->tester->get('?q=this-page-should-not-exists');
        $this->checkErrorPage($contents);
        $this->checkPageContainsError($contents, 'Request is not supported.');
        

        $this->logger->test('I am going to Logout.');

        $contents = $this->tester->get('?q=logout');
        $this->checkLoginPage($contents);
        $this->checkPageContainsMessage($contents, 'Logout success');


        $this->logger->test('I am going to a non-exists page while I am logged out to see it shows the error page.');

        $contents = $this->tester->get('?q=this-page-should-not-exists');
        $this->checkErrorPage($contents);
        $this->checkPageContainsError($contents, 'Request is not supported.');
    }

    protected function checkIfICanLogin(string $user = AppTest::USER_EMAIL, string $password = AppTest::USER_PASSWORD): string
    {
        $this->logger->test("I am going to post these login credentials: user: '$user' / password: '$password'");

        $contents = $this->tester->post('?q=', [
            'email' => $user,
            'password' => $password,
        ]);
        $this->checkPageContainsMessage($contents, 'Login success');
        return $contents;
    }

    protected function checkLoginAfterLoginShouldFails(): void
    {
        $this->logger->test('I am going to login');
        $contents = $this->checkIfICanLogin();
        $this->checkMainPage($contents);


        $this->logger->test('I am going to try login again when I am logged in so that I get an error page.');

        $contents = $this->tester->post('', [
            'email' => AppTest::USER_EMAIL,
            'password' => AppTest::USER_PASSWORD,
        ]);
        $this->checkErrorPage($contents);
        $this->checkPageContainsError($contents, 'Request is not supported.');


        $this->logger->test('I am going to Logout.');

        $contents = $this->tester->get('?q=logout');
        $this->checkLoginPage($contents);
        $this->checkPageContainsMessage($contents, 'Logout success');
    }

    protected function checkInvalidRegistryShouldFails(): void
    {
        $this->logger->test('I am going to send an empty registration');

        $contents = $this->tester->post('?q=registry', [
            'email' => '',
            'email_retype' => '',
            'password' => '',
        ]);
        $this->checkRegistryPage($contents);
        $this->checkPageContainsError($contents, 'Email can not be empty');


        $this->logger->test('I am going to send two different email address');

        $contents = $this->tester->post('?q=registry', [
            'email' => 'email@address.org',
            'email_retype' => 'email@misspelled.org',
            'password' => '',
        ]);
        $this->checkRegistryPage($contents);
        $this->checkPageContainsError($contents, 'Email fields are not the same');


        $this->logger->test('I am going to send an invalid email address');

        $contents = $this->tester->post('?q=registry', [
            'email' => 'invalid-address.org',
            'email_retype' => 'invalid-address.org',
            'password' => '',
        ]);
        $this->checkRegistryPage($contents);
        $this->checkPageContainsError($contents, 'Email address is invalid');


        $this->logger->test('I am going to send a short password');

        $contents = $this->tester->post('?q=registry', [
            'email' => 'valid@address.org',
            'email_retype' => 'valid@address.org',
            'password' => 'a',
        ]);
        $this->checkRegistryPage($contents);
        $this->checkPageContainsError($contents, 'Your Password Must Contain At Least 8 Characters!');
    

        $this->logger->test('I am going to send a password without any number');

        $contents = $this->tester->post('?q=registry', [
            'email' => 'valid@address.org',
            'email_retype' => 'valid@address.org',
            'password' => 'asdfghjkl',
        ]);
        $this->checkRegistryPage($contents);
        $this->checkPageContainsError($contents, 'Your Password Must Contain At Least 1 Number!');
      

        $this->logger->test('I am going to send a password without any capital letter');

        $contents = $this->tester->post('?q=registry', [
            'email' => 'valid@address.org',
            'email_retype' => 'valid@address.org',
            'password' => 'asdfghjkl1234',
        ]);
        $this->checkRegistryPage($contents);
        $this->checkPageContainsError($contents, 'Your Password Must Contain At Least 1 Capital Letter!');
      

        $this->logger->test('I am going to send a password without any Lowercase Letter');

        $contents = $this->tester->post('?q=registry', [
            'email' => 'valid@address.org',
            'email_retype' => 'valid@address.org',
            'password' => 'QWERTYU1234',
        ]);
        $this->checkRegistryPage($contents);
        $this->checkPageContainsError($contents, 'Your Password Must Contain At Least 1 Lowercase Letter!');


        $this->logger->test('I am going to try to registrate an already exist user');

        $contents = $this->tester->post('?q=registry', [
            'email' => AppTest::USER_EMAIL,
            'email_retype' => AppTest::USER_EMAIL,
            'password' => 'ValidPassword123!',
        ]);
        $this->checkRegistryPage($contents);
        $this->checkPageContainsError($contents, 'Registration failed');
    }

    protected function checkInvalidPasswordResetShouldFails(): void
    {
        $this->logger->test('I am going to try to send an empty email to password reset.');

        $contents = $this->tester->post('?q=pwdreset', [
            'email' => '',
        ]);
        $this->checkLoginPage($contents);
        $this->checkPageContainsError($contents, 'Request for password reset is failed');


        $this->logger->test('I am going to try to send an invalid email to password reset.');

        $contents = $this->tester->post('?q=pwdreset', [
            'email' => 'invalid-email-address',
        ]);
        $this->checkLoginPage($contents);
        $this->checkPageContainsError($contents, 'Request for password reset is failed');
    }

    protected function checkErrorPage(string $contents): void
    {
        $this->logger->test('I am going to check that I can see the Error page properly.');

        $this->tester->assertContains('<h1>Error</h1>', $contents);
        $this->tester->assertContains('<a href="' . $this->config->get('baseUrl') . '">Back</a>', $contents);
    }

    protected function checkLoginPage(string $contents): void
    {
        $this->logger->test('I am going to check that I can see the Login page properly.');

        $this->tester->assertContains('<h1>Login</h1>', $contents);
        $this->tester->assertContains('<form method="POST" action="?q=">', $contents);
        $this->tester->assertContains('<input type="email" name="email"', $contents);
        $this->tester->assertContains('<input type="password" name="password"', $contents);
        $this->tester->assertContains('<input type="submit" value="Login"', $contents);
        $this->tester->assertContains('<a href="?q=registry">Registry</a>', $contents);
        $this->tester->assertContains('<a href="?q=pwdreset">Forgotten password</a>', $contents);
    }

    protected function checkRegistryPage(string $contents): void
    {
        $this->logger->test('I am going to check that I can see the Registry page properly.');

        $this->tester->assertContains('<h1>Registry</h1>', $contents);
        $this->tester->assertContains('<input type="email" name="email"', $contents);
        $this->tester->assertContains('<input type="email" name="email_retype"', $contents);
        $this->tester->assertContains('<input type="password" name="password"', $contents);
        $this->tester->assertContains('<input type="submit" value="Register"', $contents);
        $this->tester->assertContains('<a href="?q=login">Login</a>', $contents);
        $this->tester->assertNotContains('<a href="?q=registry">Registry</a>', $contents);
        $this->tester->assertNotContains('<a href="?q=pwdreset">Forgotten password</a>', $contents);
    }

    protected function checkPasswordResetPage(string $contents): void
    {
        $this->logger->test('I am going to check that I can see the Password Reset page properly.');

        $this->tester->assertContains('<h1>Password reset</h1>', $contents);
        $this->tester->assertContains('<input type="email" name="email"', $contents);
        $this->tester->assertContains('<input type="submit" value="Reset password"', $contents);
    }

    protected function checkMainPage(string $contents): void
    {
        $this->logger->test('I am going to check that I can see the Main page properly.');

        $this->tester->assertContains('<h1>Main</h1>', $contents);
        $this->tester->assertContains('<a href="?q=logout">Logout</a>', $contents);
    }

    protected function checkChangePasswordPage(string $contents): void
    {
        $this->logger->test('I am going to check that I can see the Change Password page properly.');

        $this->tester->assertContains('<h1>Change password</h1>', $contents);
        $this->tester->assertContains('<input type="password" name="password"', $contents);
        $this->tester->assertContains('<input type="password" name="password_retype"', $contents);
        $this->tester->assertContains('<input type="submit" value="Change password"', $contents);
    }
    
    protected function checkPageContainsMessage(string $contents, string $message): void
    {
        $this->logger->test('I am going to check that I can see the message "' . $message . '" on the page.');

        $this->tester->assertContains('<div class="message">' . $message . '</div>', $contents);
    }

    protected function checkPageContainsError(string $contents, string $error): void
    {
        $this->logger->test('I am going to check that I can see the error "' . $error . '" on the page.');

        $this->tester->assertContains('<div class="message red">' . $error . '</div>', $contents);
    }

    protected function checkMail(string $subject): string
    {
        $this->logger->test('I am going to check that I have got an email with subject "' . $subject . '".');

        $files = (array)glob($this->config->get('mailerSaveMailsPath') . '/*.*');
        $this->tester->assertCount(1, $files);
        $this->tester->assertContains(AppTest::USER_EMAIL, (string)($files[0] ?? ''));
        $this->tester->assertContains($subject, (string)($files[0] ?? ''));
        return (string)($files[0] ?? '') ? (string)file_get_contents((string)($files[0] ?? '')) : '';
    }

    protected function checkRegistrationEmail(string $email): string
    {
        $this->logger->test('I am going to check that the Registration email is correct.');

        $this->tester->assertContains('Thank you for your registration,<br>', $email);
        $this->tester->assertContains('please activate your account by click on the following link or copy to your browser address line:<br>', $email);
        $this->tester->assertContains('<a href="' . $this->config->get('baseUrl') . '?q=activate&token=', $email);
        $this->tester->assertContains('">' . $this->config->get('baseUrl') . '?q=activate&token=', $email);
        $token = explode('">', explode('&token=', $email)[1] ?? '')[0] ?? '';
        $this->tester->assertLongerThan(40, $token);
        return $token;
    }

    protected function checkPasswordResetEmail(string $email): string
    {
        $this->logger->test('I am going to check that the Password Reset email is correct.');

        $this->tester->assertContains('You asked to reset your password,<br>', $email);
        $this->tester->assertContains('you can reset your password by click on the following link or copy to your browser address line:<br>', $email);
        $this->tester->assertContains('<a href="' . $this->config->get('baseUrl') . '?q=newpassword&token=', $email);
        $this->tester->assertContains('">' . $this->config->get('baseUrl') . '?q=newpassword&token=', $email);
        $this->tester->assertContains('If you did not asked to reset password please ignore this message.<br>', $email);

        $token = explode(
            '">',
            explode(
                '&token=',
                $email
            )[1]
        )[0];
        $this->tester->assertLongerThan(40, $token);
        return $token;
    }
}
