<?php

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface;

use App\Services\Auth\AuthService;

class AuthorizationMiddleware
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();

        // если есть токен то произойдет авторизация пользоватлея
        $this->authService->checkToken();
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        return $handler->handle($request);
    }
}
