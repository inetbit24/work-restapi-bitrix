<?php

namespace App\Controllers\Auth;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Services\Auth\AuthService;
use App\Rules\FactoryRule;
use App\Rules\Auth\AuthRule;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login(Request $request)
    {
        return response($this->authService->login(
            FactoryRule::build(AuthRule::class)
                ->request($request)
                ->method('login')
                ->validate(),
            $request
        ))->success();
    }

    public function logout(Request $request)
    {
        return response($this->authService->logout($request))->success();
    }

    public function accessToken(Request $request, Response $response)
    {
        return response($this->authService->accessToken($request, $response))->success();
    }
}
