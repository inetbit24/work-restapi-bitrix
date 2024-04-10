<?php

namespace App\Services\Auth;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use OAuth2Server\Repositories\AccessTokenRepository;
use OAuth2Server\Repositories\ClientRepository;
use OAuth2Server\Repositories\ScopeRepository;
use OAuth2Server\Repositories\AuthCodeRepository;
use OAuth2Server\Repositories\RefreshTokenRepository;
use OAuth2Server\Entities\UserEntity;

use Slim\App as SlimApp;
use App\Exceptions\Auth\AuthException;
use Slim\Factory\AppFactory;
use Bitrix\Main\Loader;
use Bitrix\Main\Security\Password;
use App\Services\Bitrix\BitrixService;
use App\Providers\Slim\SlimProvider;
use DI\Container;
use App\Config\Config;
use App\Services\User\UserService;
use App\Traits\Auth\Authorization;

class AuthService
{

    use Authorization;

    private static array $userAuth = [];

    private BitrixService $bitrixService;

    private UserService $userService;

    function __construct()
    {
        $this->bitrixService = new BitrixService();

        $this->userService = new UserService();
    }

    /**
     * Установим текущего авторизованного пользователя
     * 
     * @return array
     */
    public static function setUser(array $user)
    {
        self::$userAuth = $user;
    }

    /**
     * Получим авторизованного пользователя
     * 
     * @return array
     */
    public static function getUser(): array
    {
        return self::$userAuth;
    }

    /**
     * Авторизован ли пользователь
     * 
     * @return bool
     */
    public static function isAuth(): bool
    {
        return !empty(self::$userAuth);
    }


}
