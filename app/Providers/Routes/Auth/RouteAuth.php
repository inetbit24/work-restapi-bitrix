<?php

namespace App\Providers\Routes\Auth;

use Slim\Routing\RouteCollectorProxy;

use App\Providers\Slim\SlimProvider;
use App\Controllers\Auth\AuthController;
use App\Middlewares\AuthMiddleware;

class RouteAuth
{
    public function __construct(private ?RouteCollectorProxy $group = null)
    {
        ($this->group ?? SlimProvider::getApp())->post('/api/auth/login', [AuthController::class, 'login']);
        ($this->group ?? SlimProvider::getApp())->post('/api/auth/access_token', [AuthController::class, 'accessToken']);
        ($this->group ?? SlimProvider::getApp())->post('/api/auth/logout', [AuthController::class, 'logout'])->add(new AuthMiddleware());
    }
}
