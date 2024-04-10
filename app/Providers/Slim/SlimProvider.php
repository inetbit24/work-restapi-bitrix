<?php

namespace App\Providers\Slim;

use Slim\App as SlimApp;
use Slim\Factory\AppFactory;
use App\Exceptions\ExceptionHandler;

class SlimProvider
{
    private static SlimApp $appFactory;

    public static function create()
    {
        self::$appFactory = AppFactory::create();
    }

    public static function getApp(): SlimApp
    {
        return self::$appFactory;
    }

    public static function setErrorHandler()
    {
        self::$appFactory->addRoutingMiddleware();
        $errorMiddleware = self::$appFactory->addErrorMiddleware(true, true, true);
        $errorMiddleware->setDefaultErrorHandler(new ExceptionHandler());
    }

    public static function run()
    {
        self::$appFactory->run();
    }
}
