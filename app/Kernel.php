<?php

namespace App;

use Slim\Routing\RouteCollectorProxy;

use Bitrix\Main\Loader;
use App\Providers\Slim\SlimProvider;
use App\Providers\Routes\RouteProvider;
use Valitron\Validator;
use App\Config\Config;
use App\Middlewares\AuthorizationMiddleware;

class Kernel
{
    // Первоначальные установки приложения
    public function __construct()
    {
        // загрузим конфигурационный файл
        Config::init();

        // отклчим битриксовую статистику
        define('NO_AGENT_CHECK', true);
        define("STOP_STATISTICS", true);

        // подключим сам битрикс к приложухе
        require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

        // можно тут загружать все интересующие модули или
        // можно в классах сервиса там где это необходимо
        Loader::includeModule('iblock');

        // установим язык русский для валидации
        Validator::lang('ru');

        // Создадим слим 
        SlimProvider::create();
    }

    public function start()
    {
        // Если токен есть в заголовке то произойдет авторизация
        SlimProvider::getApp()->add(new AuthorizationMiddleware());

        // инициализируем глобальный отлов ошибок
        SlimProvider::setErrorHandler();

        // подключим поставщика роутов
        new RouteProvider();

        // запустим все наше приложение
        SlimProvider::run();
    }
}
