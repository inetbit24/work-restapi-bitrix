<?php

namespace App\Providers\Routes;

use Slim\Routing\RouteCollectorProxy;
use App\Providers\Slim\SlimProvider;
use App\Middlewares\AuthMiddleware;
use App\Providers\Routes\Product\RouteProduct;
use App\Providers\Routes\Auth\RouteAuth;

class RouteProvider
{
    public function __construct()
    {
        // класс с роутами авторизации
        new RouteAuth();

        // сгрупперуем все запросы к апи требующие авторизации
        SlimProvider::getApp()->group('', function (RouteCollectorProxy $group) {
            // класс с роутами работы с каталогом
            new RouteProduct($group);
        })->add(new AuthMiddleware()); // сам мидлвеар авторизации
    }
}
