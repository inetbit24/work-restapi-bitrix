<?php

namespace App\Services\User;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Exceptions\Auth\AuthException;
use Bitrix\Main\Security\Password;
use App\Services\Bitrix\BitrixService;
use App\Config\Config;

class UserService
{
    private BitrixService $bitrixService;

    function __construct()
    {
        $this->bitrixService = new BitrixService();
    }

    /**
     * Получим токен - роли, доступы и самого пользователя
     * 
     * @param string $token - логин пользователя
     * @return array
     */
    public function getUser(array $filter, bool $updateToken = false): array
    {
        $time = time() - 3600 * 24 * Config::key('TIME_AUTH');

        /** Проверяем наличие токена **/
        $entity = $this->bitrixService->getEntity(Config::key('HLB_AUTH'));

        $resToken = $entity::getList([
            'select' => ['ID', 'UF_USER_LOGIN', 'UF_USER_TOKEN'],
            'filter' => $filter + ['>UF_USER_EXPIRES' => $time],
        ]);

        if ($arToken = $resToken->fetch()) {

            if ($updateToken) {
                $entity::update($arToken['ID'], ['UF_USER_EXPIRES' => (time() + 3600 * 24 * Config::key('TIME_AUTH'))]);
            }

            return $this->getFullUserData($arToken['UF_USER_LOGIN']) + ['TOKEN' => $arToken['UF_USER_TOKEN']];
        }

        return [];
    }

    /**
     * Получим пользователя по токену - роли, доступы и самого пользователя
     * 
     * @param string $token - токен пользователя
     * @return array
     */
    public function getUserByToken(string $token): array
    {
        /** Проверяем наличие токена **/

        return $this->getUser(['UF_USER_TOKEN' => $token]);
    }

    /**
     * Получим пользователя по логину - роли, доступы и самого пользователя
     * 
     * @param string $login - логин пользователя
     * @return array
     */
    public function getUserByLogin(string $login): array
    {
        /** Проверяем наличие токена **/

        return $this->getUser(['UF_USER_LOGIN' => $login], true);
    }

    /**
     * Получим пользователя по логин пользователя или его id - роли, доступы и самого пользователя
     * 
     * @param string | int $user - логин пользователя или его id
     * @return array
     */
    public function getFullUserData(string | int $user): array
    {
        $arUser = [];

        $where = 'LOGIN';

        if (is_numeric($user)) {
            $where = 'ID';
        }

        $resQuery = $GLOBALS['DB']->Query("SELECT `b_user`.`ID` as `ID`, `b_user`.`NAME` as `NAME`, `b_user`.`LOGIN` as `LOGIN`,
            `" . Config::key('DB_ROLES') . "`.`UF_NAME` as `ROLE_NAME`, `" . Config::key('DB_ROLES') . "`.`UF_CODE` as `ROLE_CODE`,
            `" . Config::key('DB_PERMISSIONS') . "`.`UF_NAME` as `PERMISSION_NAME`, `" . Config::key('DB_PERMISSIONS') . "`.`UF_CODE` as `PERMISSION_CODE`
	        FROM `b_user`
            LEFT JOIN `" . Config::key('DB_ROLE_USER') . "` ON `" . Config::key('DB_ROLE_USER') . "`.`UF_USER` = `b_user`.`ID`
            LEFT JOIN `" . Config::key('DB_ROLES') . "` ON `" . Config::key('DB_ROLES') . "`.`ID` = `" . Config::key('DB_ROLE_USER') . "`.`UF_ROLE`
            LEFT JOIN `" . Config::key('DB_PERMISSION_ROLE') . "` ON `" . Config::key('DB_PERMISSION_ROLE') . "`.`UF_ROLE` = `" . Config::key('DB_ROLES') . "`.`ID`
            LEFT JOIN `" . Config::key('DB_PERMISSIONS') . "` ON `" . Config::key('DB_PERMISSIONS') . "`.`ID` = `" . Config::key('DB_PERMISSION_ROLE') . "`.`UF_PERMISSION`
            WHERE `b_user`.`$where` = '$user'", false);


        $bufUser = [];

        while ($row = $resQuery->Fetch()) {
            if (empty($bufUser)) {
                $bufUser[$row['ID']] = [
                    "ID" => $row['ID'],
                    "NAME" => $row['NAME'],
                    "LOGIN" => $row['LOGIN'],
                ];
            }

            $bufUser[$row['ID']]['ROLES'][$row['ROLE_CODE']] = [
                'NAME' => $row['ROLE_NAME'],
                'CODE' => $row['ROLE_CODE']
            ];

            $bufUser[$row['ID']]['PERMISSIONS'][$row['PERMISSION_CODE']] = [
                'NAME' => $row['PERMISSION_NAME'],
                'CODE' => $row['PERMISSION_CODE']
            ];
        }

        $arUser = current($bufUser);
        $arUser['ROLES'] = [];
        $arUser['PERMISSIONS'] = [];

        foreach (current($bufUser)['ROLES'] as $value) {
            $arUser['ROLES'][] = $value;
        }

        foreach (current($bufUser)['PERMISSIONS'] as $value) {
            $arUser['PERMISSIONS'][] = $value;
        }

        return $arUser;
    }
}
