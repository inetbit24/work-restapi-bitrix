<?php

namespace App\Services\Bitrix;

use App\Exceptions\Bitrix\BitrixException;
use Bitrix\Main\Loader;
use Bitrix\Main\Data\Cache;
use Bitrix\Highloadblock\HighloadBlockTable;

class BitrixService
{

    function __construct()
    {
        Loader::includeModule('iblock');
        Loader::includeModule('highloadblock');
    }

    public static function init(): self
    {
        return new self;
    }

    /**
     * Получаем сущность для работы с HLB
     */
    public function getEntity($sEntity): mixed
    {
        $obCache = Cache::createInstance();
        $cache_time = '86400';
        $cache_id = "entity_{$sEntity}";

        if ($obCache->initCache($cache_time, $cache_id, 'api')) {

            $arEntity = $obCache->GetVars();
            $oEntity = HighloadBlockTable::compileEntity($arEntity)->getDataClass();

            return $oEntity;
        } else if ($obCache->startDataCache()) {

            $oRes = HighloadBlockTable::getList(['filter' => ['NAME' => $sEntity]]);

            if ($arEntity = $oRes->fetch()) {

                $oEntity = HighloadBlockTable::compileEntity($arEntity)->getDataClass();
                $obCache->endDataCache($arEntity);

                return $oEntity;
            } else {
                $obCache->abortDataCache();
            }
        }

        throw new BitrixException("Сущность $sEntity для работы с HLB не найдена");
    }

    /**
     * Формируем список ID разделов
     */
    function getProductSectionId($id)
    {
        $sections = [];
        $resNav = \CIBlockSection::GetNavChain(false, $id);
        while ($arNav = $resNav->fetch()) {
            $sections[] = $arNav['ID'];
        }

        return $sections;
    }
}
