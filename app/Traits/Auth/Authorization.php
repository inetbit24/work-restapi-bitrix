<?php

namespace App\Traits\Auth;

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

trait Authorization
{
    /**
     * Инициализируем объект авторизации через паттерн контейнеров
     * https://github.com/thephpleague/oauth2-server/tree/master
     * 
     * @param  string  $type
     * @return SlimApp
     */
    private function initAuthorizationServer(string $type = 'clientGrant'): SlimApp
    {
        $container = new Container();

        $container->set(
            AuthorizationServer::class,
            function () use ($type) {
                $clientRepository = new ClientRepository();
                $scopeRepository = new ScopeRepository();
                $accessTokenRepository = new AccessTokenRepository();
                $authCodeRepository = new AuthCodeRepository();
                $refreshTokenRepository = new RefreshTokenRepository();


                if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/api/private.key')) {
                    $privateKey = 'file://' . $_SERVER['DOCUMENT_ROOT'] . '/api/private.key';
                } else {
                    throw new AuthException('Отсутствует файл приватного ключа для сервера авторизации.');
                }

                // Настройка сервера авторизации
                $server = new AuthorizationServer(
                    $clientRepository,
                    $accessTokenRepository,
                    $scopeRepository,
                    $privateKey,
                    'lxZFUEsBCJ2Yb14IF2ygAHI5N4+ZAUXXaSeeJm6+twsUmIen'
                );

                if ($type == 'clientGrant') {
                    // Включите предоставление учетных данных клиента на сервере
                    $server->enableGrantType(
                        new \League\OAuth2\Server\Grant\ClientCredentialsGrant(),
                        new \DateInterval('P' . Config::key('TIME_AUTH') . 'D') // установка время жизни токена
                    );
                } else {
                    // Включите выдачу кода аутентификации на сервере с TTL токена в TIME_AUTH дней
                    $server->enableGrantType(
                        new \League\OAuth2\Server\Grant\AuthCodeGrant(
                            $authCodeRepository,
                            $refreshTokenRepository,
                            new \DateInterval('P' . Config::key('TIME_AUTH') . 'D') // установка время жизни токена
                        ),
                        new \DateInterval('P30D')
                    );
                }

                return $server;
            }
        );

        // записываем контейнер
        AppFactory::setContainer($container);

        // создаем повторный вызов слима фабрики
        $app = AppFactory::create();

        return $app;
    }

    /**
     * Запрос токена по логину и паролю
     * 
     * @param  array  $params
     * @param  Request  $request
     * @return array
     */
    public function login(array $params, Request $request): array
    {
        // сначала ппытаемся найти клиента по логину
        if ($this->validateClient($params['login'], $params['password'])) {
            // проверяем есть ли уже токен в бд
            $user = $this->userService->getUserByLogin($params['login']);

            if (!empty($user)) {
                return $user;
            }
        }

        /*
            https://oauth2.thephpleague.com/authorization-server/resource-owner-password-credentials-grant/
            
            Клиент запросит у пользователя его учетные данные для авторизации (обычно имя пользователя и пароль).
            Затем клиент отправляет POST-запрос со следующими параметрами тела на сервер авторизации:
            Это предоставление подходит для межмашинной аутентификации, например, для использования в задании cron, которое выполняет задачи обслуживания через API. Другим примером может служить клиент, отправляющий запросы к API, которые не требуют разрешения пользователя.
        */

        $uri = $request->getUri();
        $port = $uri->getPort();
        $port = !empty($port) ? '' : ':' . $port;

        //Клиент запросит токен доступа, поэтому создайте /access_token конечную точку.
        $curlUrl = $uri->getScheme() . '://' . $uri->getHost() . $port . '/api/auth/access_token';

        $arParams = [
            'client_id' => $params['login'],
            'client_secret' => $params['password'],
            'grant_type' => 'client_credentials',
            'scope' => 'basic'
        ];

        $curl = curl_init(); // Инициализируем запрос
        curl_setopt_array($curl, [
            CURLOPT_URL => $curlUrl, // Полный адрес метода
            CURLOPT_POST => true, // Метод POST
            CURLOPT_POSTFIELDS => http_build_query($arParams), // Данные в запросе,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $curlResult = json_decode(curl_exec($curl));
   
        if (!empty($curlResult->error)) {
            throw new AuthException($curlResult->message);
        }

        return $this->userService->getUserByToken($curlResult->data->access_token);
    }

    /**
     * Получение токена 
     * 
     * @param  Request  $request
     * @param  Response  $response
     * @return mixed
     */
    public function accessToken(Request $request, Response $response): mixed
    {
        $app = $this->initAuthorizationServer('clientGrant');

        $server = $app->getContainer()->get(AuthorizationServer::class);

        try {
            // Ответить на запрос accessToken
            $server->respondToAccessTokenRequest($request, $response);
            $arReturn = json_decode($response->getBody());
            return $arReturn;
        } catch (OAuthServerException $exception) {
            throw new AuthException($exception->getMessage());
        } catch (\Exception $exception) {
            throw new AuthException($exception->getMessage());
        }
    }

    /**
     * Удаление токена
     * 
     * @param  Request  $request
     * @return bool
     */
    public function logout(Request $request): bool
    {
        $headerToken = $request->getHeaderLine('Authorization');
        $arToken = explode(' ', $headerToken);

        if (!empty($arToken[1])) {
            $bResult = $this->deleteToken($arToken[1]);
            if (!$bResult) {
                throw new AuthException('Токен не найден.');
            }
        }

        return true;
    }

    /**
     * Используется в мидлвеаре для идентификации токена
     * 
     * @return bool
     */
    public function checkToken(): bool
    {
        $isCheck = false;

        // получаем из заголовка Authorization наш токен
        $arToken = explode(' ', getallheaders()['Authorization']);

        if (!empty($arToken[1])) {

            // найдем пользователя по токену, так же получим все доступы и роли
            if ($arUser = $this->userService->getUserByToken($arToken[1])) {

                // запишим пользователя в переменную, что будет доступна из любой точки
                self::setUser($arUser);

                /** Если пользователь существует, и администратор **/
                $isCheck = true;
            } else {
                $isCheck = false;
            }
        }

        return $isCheck;
    }

    /**
     * Удаляем просроченные токены
     */
    public function deleteExpiredToken()
    {
        $entity = $this->bitrixService->getEntity(Config::key('HLB_AUTH'));

        $resToken = $entity::getList(array(
            'select' => ['*'],
            'filter' => ['<UF_USER_EXPIRES' => time()],
        ));

        while ($arToken = $resToken->fetch()) {
            $entity::delete($arToken['ID']);
        }
    }

    /**
     * Валидация пользователя перед выдачей токена
     * Если пользователь не существует, или не администратор, или пароль не совпал, не выдаем токен 
     * 
     * @param  $clientIdentifier - логин пользователя
     * @param  $clientSecret - пароль
     * @return bool
     */
    public function validateClient($clientIdentifier, $clientSecret): bool
    {
        $resUser = \CUser::GetList(($by = 'id'), ($order = 'desc'), ['LOGIN_EQUAL' => $clientIdentifier], ['PASSOWRD']);
        $arUser = $resUser->Fetch();
        if (!$arUser || !Password::equals($arUser['PASSWORD'], $clientSecret)) {
            /** Если пользователь не существует, или не администратор, или пароль не совпал, не выдаем токен **/
            return false;
        }

        return true;
    }

    /**
     * Сохраняем токен в HLB
     */
    public function addToken($accessToken, $thisAccessToken)
    {
        $expiry = $thisAccessToken->getExpiryDateTime();

        $entity = $this->bitrixService->getEntity(Config::key('HLB_AUTH'));
        $entity::add([
            'UF_USER_LOGIN' => $thisAccessToken->getClient()->getIdentifier(),
            'UF_USER_TOKEN' => $accessToken,
            'UF_USER_EXPIRES' => strtotime($expiry->format('Y-m-d H:i:s'))
        ]);
    }

    /**
     * Удаление токена
     * 
     * @param  string $token
     * @return bool
     */
    public function deleteToken(string $token): bool
    {
        $entity = $this->bitrixService->getEntity(Config::key('HLB_AUTH'));

        $resToken = $entity::getList(array(
            'select' => array('ID'),
            'filter' => array('UF_USER_TOKEN' => $token),
        ));

        if ($arToken = $resToken->fetch()) {
            $entity::delete($arToken['ID']);
            return true;
        }

        return false;
    }
}
