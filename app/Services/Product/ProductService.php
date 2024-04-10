<?php

namespace App\Services\Product;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Bitrix\Main\Loader;
use App\Services\Bitrix\BitrixService;

class ProductService
{
    private BitrixService $bitrixService;

    function __construct()
    {
        Loader::includeModule('iblock');

        $this->bitrixService = new BitrixService();
    }

    public function index(): array
    {
        $arResult = [];

        $rsList = \CIBlockElement::GetList([], ['IBLOCK_ID' => 2], false, false, ['ID', 'IBLOCK_ID', 'NAME']);
        while ($arList = $rsList->fetch()) {
            $arResult[] = $arList;
        }

        return $arResult;
    }

    public function store(array $params): array
    {
        $instanceElement = new \CIBlockElement();

        if (empty($params['code'])) {
            $params['code'] = \Cutil::translit($params['name'], "ru");
        }

        $arProps = [];

        /** Формируем массив дополнительных параметров для вставки **/
        $arSection = [];
        $sections = explode(',', $params['sections']);
        /** Получаем ID всей цепочки разделов в иерархии **/
        foreach ($sections as $section) {
            $arSection = array_merge($arSection, $this->bitrixService->getProductSectionId($section));
        }

        if (!empty($params['properties'])) {
            foreach ($params['properties'] as $keyProperty => $valProperty) {
                switch ($keyProperty) {
                    case 'background_image':
                        $params['properties'][mb_strtoupper($keyProperty)] = \CFile::MakeFileArray($valProperty);
                        break;
                    default:
                        $params['properties'][mb_strtoupper($keyProperty)] = $valProperty;
                        break;
                }
            }
        }

        $arFields = [
            'IBLOCK_SECTION_ID' => current($sections),
            'IBLOCK_SECTION' => $sections,
            'IBLOCK_ID' => $params['iblock_id'],
            'NAME' => $params['name'],
            'CODE' => $params['code'],
            'ACTIVE' => 'Y',
            // 'PREVIEW_TEXT' => $arParams['preview_text'],
            // 'DETAIL_TEXT' => $arParams['detail_text'],
            // "PREVIEW_PICTURE" => $arImg,
            // "DETAIL_PICTURE" => $arImg,
            'PROPERTY_VALUES' => $params['properties']
        ];

        $elementId = $instanceElement->Add($arFields);

        dump($elementId);

        dd($params);

        return [];
    }
}
