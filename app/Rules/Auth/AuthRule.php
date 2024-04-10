<?php

namespace App\Rules\Auth;

use Psr\Http\Message\ServerRequestInterface as Request;

use App\Rules\FormRule;


class AuthRule extends FormRule
{

    public function login(): array
    {
        
        $this->validator->rule('required', 'login');
        $this->validator->rule('required', 'password');

        return $this->validate();
    }
}
