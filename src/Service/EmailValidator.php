<?php declare(strict_types = 1);

namespace Madsoft\App\Service;

class EmailValidator
{
    public function isValidEmail(string $email): string
    {
        return (string)filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
