<?php

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface;

use App\Services\Auth\AuthService;
use App\Exceptions\Auth\AuthException;

class AuthMiddleware
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        if (!AuthService::isAuth()) {
            throw new AuthException();
        }
    
        return $handler->handle($request);
    }
}
