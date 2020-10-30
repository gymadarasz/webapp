<?php

namespace Madsoft\App;

use Exception;
use RuntimeException;
use Madsoft\App\Logger;
use Madsoft\App\Globals;
use Madsoft\App\Mysql;
use Madsoft\App\User;
use Madsoft\App\Mailer;
use Madsoft\App\Template;

class Router
{

    /** @var array<mixed> $routes */
    private array $routes;

    /**
     * @param array<mixed> $routes
     */
    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }
}

class App
{
    private Logger $logger;
    private Globals $globals;
    private Mysql $mysql;
    private User $user;
    private Mailer $mailer;

    private Template $output;

    public function __construct(Logger $logger, Globals $globals, Mysql $mysql, User $user, Mailer $mailer)
    {
        $this->logger = $logger;
        try {
            $this->globals = $globals;
            $this->mysql = $mysql;
            $this->user = $user;
            $this->mailer = $mailer;
            $this->routing();
        } catch (Exception $e) {
            $this->logger->doLogException($e);
        }
    }

    private function routing(): void
    {
        $this->globals->sessionStart();
        if ($this->globals->getSession('user', false)) {
            switch ($this->globals->getMethod()) {
                
                case 'GET':
                    $this->doGetRestricted();
                break;

                case 'POST':
                    $this->doPostRestricted();
                break;

                default:
                    $this->output = new Template('error-page.html.php');
                    $this->output->set('error', 'Requested method is not supported.');
            }
        } else {
            switch ($this->globals->getMethod()) {
                
                case 'GET':
                    $this->doGet();
                break;

                case 'POST':
                    $this->doPost();
                break;

                default:
                    $this->output = new Template('error-page.html.php');
                    $this->output->set('error', 'Requested method is not supported.');
            }
        }
        if (!isset($this->output)) {
            $this->output = new Template('error-page.html.php');
            $this->output->set('error', 'Request does not produces any response.');
        }
        $this->output->set('base', Config::get('baseUrl'));
    }

    private function doGet(): void
    {
        switch ($this->globals->getGet('q')) {
            case '':
            case 'login':
                $this->output = new Template('login.html.php');
            break;
            case 'registry':
                $this->output = new Template('registry.html.php');
            break;
            case 'activate':
                $this->mysql->connect();
                if ($this->user->doActivate($this->globals->getGet('token'))) {
                    $this->output = new Template('login.html.php');
                    $this->output->set('message', 'Your account is now activated.');
                } else {
                    $this->output = new Template('error-page.html.php');
                    $this->output->set('error', 'Activation token is incorrect.');
                }
            break;
            case 'pwdreset':
                $this->output = new Template('pwdreset.html.php');
            break;
            case 'newpassword':
                $token = $this->globals->getGet('token');
                $this->mysql->connect();
                if (!$this->user->doAuthByToken($token)) {
                    throw new UserErrorException('Invalid token');
                }
                $this->output = new Template('pwdchange.html.php');
            break;
            case 'resend':
                $this->output = new Template('login.html.php');
                $this->output->setAsItIs('message', 'Attempt to resend activation email, please check your email inbox and validate your account, or try to resend by <a href="?q=resend">click here</a>');
        
                $resend = $this->globals->getSession('resend');
                $email = $resend['email'];
                $token = $resend['token'];
                if (!$this->sendActivationEmail($email, $token)) {
                    throw new UserErrorException('Email sending error!');
                }
            break;
            default:
                $this->output = new Template('error-page.html.php');
                $this->output->set('error', 'Requested public page is not supported.');
        }
    }

    private function doPost(): void
    {
        switch ($this->globals->getGet('q')) {
            case '':
            case 'login':
                $this->mysql->connect();
                if ($this->user->doAuth(
                    $this->globals->getPost('email', ''),
                    $this->globals->getPost('password', '')
                )) {
                    $this->output = new Template('index.html.php');
                    $this->output->set('message', 'Login success');
                } else {
                    $this->output = new Template('login.html.php');
                    $this->output->set('error', 'Login failed');
                }
            break;
            case 'registry':
                try {
                    $this->doRegister(
                        $this->globals->getPost('email', ''),
                        $this->globals->getPost('email_retype', ''),
                        $this->globals->getPost('password', '')
                    );
                    $this->output = new Template('login.html.php');
                    $this->output->setAsItIs('message', 'Registration success, please check your email inbox and validate your account, or try to resend by <a href="?q=resend">click here</a>');
                } catch (UserErrorException $e) {
                    $this->output = new Template('registry.html.php');
                    $this->output->set('error', $e->getMessage());
                    $this->output->set('email', $this->globals->getPost('email'));
                    $this->output->set('emailRetype', $this->globals->getPost('email_retype'));
                } catch (Exception $e) {
                    $this->logger->doLogException($e);
                    $this->output = new Template('registry.html.php');
                    $this->output->set('error', 'Registration failed');
                }
            break;
            case 'pwdreset':
                $this->output = new Template('login.html.php');
                if ($this->doPasswordReset($this->globals->getPost('email', ''))) {
                    $this->output->set('message', 'We sent an email to your inbox, please follow the given instructions to change your password');
                } else {
                    $this->output->set('error', 'Request for password reset is failed');
                }
            break;
            case 'newpassword':
                $token = $this->globals->getGet('token');
                $this->mysql->connect();
                if (!$this->user->doAuthByToken($token)) {
                    throw new UserErrorException('Invalid token');
                }
                $password = $this->globals->getPost('password');
                $passwordRetype = $this->globals->getPost('password_retype');
                $error = false;
                if ($password != $passwordRetype) {
                    $error = 'Two password are not identical';
                }
                if ($passwordError = $this->getPasswordError($password)) {
                    $error = $passwordError;
                }
                if (!$error && $this->user->changePassword($password)) {
                    $this->output = new Template('login.html.php');
                    $this->output->set('message', 'Your password changed, please log in');
                } else {
                    $this->output = new Template('pwdchange.html.php');
                    $this->output->set('error', $error);
                }
                
            break;
            default:
                $this->output = new Template('error-page.html.php');
                $this->output->set('error', 'Requested public action is not supported.');
        }
    }

    private function doGetRestricted(): void
    {
        switch ($this->globals->getGet('q')) {
            case '':
                $this->output = new Template('index.html.php');
            break;
            case 'logout':
                $this->globals->sessionDestroy();
                $this->output = new Template('login.html.php');
                $this->output->set('message', 'Logout success');
            break;
            default:
                $this->output = new Template('error-page.html.php');
                $this->output->set('error', 'Requested page is not supported.');
        }
    }

    private function doPostRestricted(): void
    {
        switch ($this->globals->getGet('q')) {
            default:
                $this->output = new Template('error-page.html.php');
                $this->output->set('error', 'Requested action is not supported.');
        }
    }

    private function doRegister(string $email, string $emailRetype, string $password): void
    {
        if ($email !== $emailRetype) {
            throw new UserErrorException('Email fields are not the same!');
        }
        if (!$this->isValidEmail($email)) {
            throw new UserErrorException('Email is not valid (' . $email . ')!');
        }
        if ($passwordError = $this->getPasswordError($password)) {
            throw new UserErrorException($passwordError);
        }
        $this->mysql->connect();
        $token = $this->user->createUser($email, $password);
        $this->globals->setSession('resend', [
            'email' => $email,
            'token' => $token,
        ]);
        if (!$token) {
            throw new UserErrorException('Registration error!');
        }
        if (!$this->sendActivationEmail($email, $token)) {
            throw new UserErrorException('Email sending error!');
        }
    }

    private function doPasswordReset(string $email): bool
    {
        if (!$this->isValidEmail($email)) {
            throw new UserErrorException('Email is not valid (' . $email . ')!');
        }
        $this->mysql->connect();
        $token = $this->user->createToken($email);
        if (!$token) {
            return false;
        }
        if (!$this->sendPasswordResetEmail($email, $token)) {
            return false;
        }
        return true;
    }

    private function isValidEmail(string $email): string
    {
        return (string)filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    private function getPasswordError(string $password): string
    {
        $passwordErr = '';
        if (strlen($password) < 8) {
            $passwordErr = "Your Password Must Contain At Least 8 Characters!";
        } elseif (!preg_match("#[0-9]+#", $password)) {
            $passwordErr = "Your Password Must Contain At Least 1 Number!";
        } elseif (!preg_match("#[A-Z]+#", $password)) {
            $passwordErr = "Your Password Must Contain At Least 1 Capital Letter!";
        } elseif (!preg_match("#[a-z]+#", $password)) {
            $passwordErr = "Your Password Must Contain At Least 1 Lowercase Letter!";
        }
        return $passwordErr;
    }

    private function sendActivationEmail(string $email, string $token): bool
    {
        $message = new Template('emails/activation.html.php');
        $message->setAsItIs('link', Config::get('baseUrl') . "?q=activate&token=$token");
        return $this->mailer->send($email, 'Activate your account', $message);
    }

    private function sendPasswordResetEmail(string $email, string $token): bool
    {
        $message = new Template('emails/pwd-reset.html.php');
        $message->setAsItIs('link', Config::get('baseUrl') . "?q=newpassword&token=$token");
        return $this->mailer->send($email, 'Password reset request', $message);
    }

    public function __toString()
    {
        return (string)$this->output;
    }
}
