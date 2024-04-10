<?php

namespace App\Exceptions\Auth;

class AuthException extends \Exception
{
    protected $code = 401;

    protected $message = 'Unauthorized';
}
