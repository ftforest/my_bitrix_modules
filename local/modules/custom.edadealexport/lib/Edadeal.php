<?php

namespace EdadealExportFile;

class Edadeal
{
    public static $module_id = 'custom.edadealexport';
    public static $codeLisenArraySettings = [];

    public static function getOptionsProduct ()
    {
        Edadeal::$codeLisenArraySettings['field_name_file_output'] = \COption::GetOptionString(Edadeal::$module_id, "field_name_file_output", "edadealexport.json");
        Edadeal::$codeLisenArraySettings['date_end_conditions'] = \COption::GetOptionString(Edadeal::$module_id, "date_end_conditions", date('d.m.Y', strtotime('+5 days')));
        Edadeal::$codeLisenArraySettings['torgovie_mark'] = \COption::GetOptionString(Edadeal::$module_id, "torgovie_mark", "");
        Edadeal::$codeLisenArraySettings['text_replace'] = \COption::GetOptionString(Edadeal::$module_id, "text_replace", "");
        Edadeal::$codeLisenArraySettings['name_cats'] = \COption::GetOptionString(Edadeal::$module_id, "name_cats", "");
    }

    public static function CreateEdadealFile()
    {
        Edadeal::getOptionsProduct();
        $arrayNameCats = Edadeal::ReaplaceCategoriesName();

        $iblockId = 114;
        // var
        $file_edadeal = []; // final array
        $ed_regions = [];
        $ed_catalogs = [];
        $ed_catalog_item = [];
        $ed_offers = [];
        $ed_offers_id = [];
        $arSelect = ['ID', 'NAME','OFFERS', 'CONDITIONS','IMAGE.FILE','TARGET_REGIONS',
            'DATA_END', 'DATA_START','IS_MAIN', 'DETAIL_PICTURE'];

        $arSelect = ['ID', "IBLOCK_ID", 'NAME', 'DETAIL_PICTURE', 'DATE_ACTIVE_FROM', 'DATE_ACTIVE_TO', "PROPERTY_*"];
        $arSelect = ['*'];
        //37780
        $arFilter = Array("IBLOCK_ID"=>$iblockId, "ACTIVE"=>"Y"); // aspro_next_content
        $resEl = \CIBlockSection::GetList(array('left_margin' => 'asc'),$arFilter);

        $items = [];
        $countProducts = 0;
        $offersId = [];
        $offers = [];
        $currentUrl = "https://yarbox.ru";
        $catalogsInfo = [];
        if (true)
            while($arEl = $resEl->GetNextElement()) {
                $countProducts++;
                $arFields = $arEl->GetFields();
                $arProps = $arEl->GetProperties();
                if (false) {
                    echo $arFields['ID'].'<br>';
                    echo $arFields['NAME'].'<br>';
                    echo $arFields['DEPTH_LEVEL'].'<br>';
                    echo "<pre>";
                    //print_r($arFields);
                    echo "</pre>";
                }

                $dateEndConditions = Edadeal::$codeLisenArraySettings['date_end_conditions']." 23:59:59";
                $dateConditions = Edadeal::DateFromTo($arFields['DATE_CREATE'],$dateEndConditions);


                if (in_array($arFields['NAME'],$arrayNameCats)) $catalogsInfo[$arFields['ID']]['conditions'] = Edadeal::$codeLisenArraySettings['text_replace'];
                else $catalogsInfo[$arFields['ID']]['conditions'] = $arFields['NAME'];
                $catalogsInfo[$arFields['ID']]['date_end'] = $dateConditions[1];
                $catalogsInfo[$arFields['ID']]['date_start'] = $dateConditions[0];
                $catalogsInfo[$arFields['ID']]['id'] = $arFields['ID'];
                $catalogsInfo[$arFields['ID']]['image'] = "https://yarbox.ru/logo.png";
                if ($arFields['DEPTH_LEVEL'] == 1) $catalogsInfo[$arFields['ID']]['is_main'] = true;
                else $catalogsInfo[$arFields['ID']]['is_main'] = false;
                $catalogsInfo[$arFields['ID']]['target_shops'] = [];

            }

        $catalogsOffersIds = [];

        $arSelectElement = ['ID', 'NAME', 'DETAIL_PICTURE', 'DETAIL_PAGE_URL', 'PROPERTY_*'];
        $arSelectElement = ['*'];
        $arFilterElement = Array("IBLOCK_ID"=>$iblockId, "ACTIVE"=>"Y");
        $indexBlock = 0;
        $resElement = \CIBlockElement::GetList(Array(), $arFilterElement, false, false, $arSelect);
        while($arEl = $resElement->GetNextElement()) {
            //if ($indexBlock >= 10) break;

            $indexBlock++;
            $offerFields = $arEl->GetFields();
            $offerProps = $arEl->GetProperties();
            if (in_array($offerProps['TORGOVAJAMARKA']['VALUE'],explode(",",trim(Edadeal::$codeLisenArraySettings['torgovie_mark'])))) continue;
            $resEl = \CCatalogProduct::GetByID($offerFields['ID']);

            $arMeasure = \Bitrix\Catalog\ProductTable::getCurrentRatioWithMeasure($offerFields["ID"]);
            if (false) {
                //if ($offerFields['ID'] != 40348) continue;
                echo "<pre>";
                //print_r($resEl);
                print_r($offerFields);
                print_r($offerProps);
                echo "</pre>";
                echo "ID: ".$offerFields['ID']."<br>";
                echo "NAME: ".$offerFields['NAME']."<br>";
                echo "TOTAL_PRICE_ALL: ".$offerFields['TOTAL_PRICE_ALL']['VALUE']."<br>";
                echo "RATIO: ".$arMeasure[$offerFields["ID"]]['RATIO']."<br>";
                echo "price_new: ".$offerProps['TOTAL_PRICE_ALL']['VALUE']*$arMeasure[$offerFields["ID"]]['RATIO']."<br>";
                die();
            }
            $catalogsOffersIds[$offerFields['IBLOCK_SECTION_ID']]['offers_id'][] = $offerFields['ID'];
            $imgSrc = \CFile::GetPath($offerFields["DETAIL_PICTURE"]);



            $offerItem = [
                //"barcode" => $offerProps["BARCODE"]['VALUE'],
                //"date_end" => $offerProps["DATE_EXPIRATION"]['VALUE'],
                //"date_start" => "2020-06-05T00:00:00+03:00",
                "description" => $offerFields['NAME'],
                //"discount_label" => "1+1",
                "id" => $offerFields['ID'],
                //"image" => $currentUrl.$imgSrc,
                //"price_is_from" => false,
                "price_new" => $offerProps['TOTAL_PRICE_ALL']['VALUE']*$arMeasure[$offerFields["ID"]]['RATIO'],
            ];

            if ($imgSrc == '') $offerItem["image"] = "https://yarbox.ru/logo.png";
            else $offerItem["image"] = $currentUrl.$imgSrc;

            if ($offerProps['DISCOUNTED']['VALUE'] == 'Y') {
                $offerItem["price_old"] = $offerProps['TOTAL_PRICE_ALL']['VALUE']*$arMeasure[$offerFields["ID"]]['RATIO']
                    + $offerProps['DISCOUNT_AMOUNT']['VALUE']*$arMeasure[$offerFields["ID"]]['RATIO'];
            }
            $offerItem["url"] = $currentUrl.$offerFields["DETAIL_PAGE_URL"];
            $offers[] = $offerItem;
        }

        foreach ($catalogsInfo as $id => $catalog) {

            /*if (empty($catalogsOffersIds[$id]['offers_id'])) $offersIdCat = [];
            else $offersIdCat = $catalogsOffersIds[$id]['offers_id'];*/

            if (!empty($catalogsOffersIds[$id]['offers_id'])) {
                $ed_catalogs[] = [
                    "conditions" => $catalog['conditions'],
                    "date_end" => $catalog['date_end'],
                    "date_start" => $catalog['date_start'],
                    "id" => $catalog['id'],
                    "image" => $catalog['image'],
                    "is_main" => $catalog['is_main'],
                    "offers" => $catalogsOffersIds[$id]['offers_id'],
                    "target_regions" => [
                        "Россия, Красноярск",
                        "Россия, Красноярский край, Сосновоборск",
                        "Россия, Красноярский край, Емельяново"
                    ],
                    "delivery_regions" => [
                        "Россия, Красноярск",
                        "Россия, Красноярский край, Сосновоборск",
                        "Россия, Красноярский край, Емельяново"
                    ]
                ];
            }

        }

        $file_edadeal = [
            "catalogs" =>  $ed_catalogs,
            "offers" =>  $offers,
            "version" =>  2
        ];

        if(isset(Edadeal::$codeLisenArraySettings['field_name_file_output']) && (string)Edadeal::$codeLisenArraySettings['field_name_file_output'])
            $name_file_output = trim(Edadeal::$codeLisenArraySettings['field_name_file_output']);

        $file = new \Bitrix\Main\IO\File(\Bitrix\Main\Application::getDocumentRoot() . "/bitrix/catalog_export/edadeal_json/".$name_file_output);
        $file->putContents(json_encode($file_edadeal,JSON_UNESCAPED_UNICODE));
    }

    public static function DateFromTo ($from, $to) {
        $dateStartCondition = '';
        $dateEndCondition = '';
        if (isset($from) && preg_match('/[:]/i',$from)) {
            $dateStart = \DateTime::createFromFormat('d.m.Y H:i:s', $from);
            $dateStartCondition = date(DATE_RFC3339, $dateStart->getTimestamp());
        } else if (isset($from)) {
            $dateStart = \DateTime::createFromFormat('d.m.Y', $from);
            $dateStartCondition = date(DATE_RFC3339, $dateStart->getTimestamp());
        }
        if (isset($to) && preg_match('/[:]/i',$to)) {
            $dateEnd = \DateTime::createFromFormat('d.m.Y H:i:s', $to);
            //$dateEnd->add(new DateInterval('P10D'));
            $dateEndCondition = date(DATE_RFC3339, $dateEnd->getTimestamp());
        } else if (isset($to)) {
            $dateEnd = \DateTime::createFromFormat('d.m.Y', $to);
            //$dateEnd->add(new DateInterval('P10D'));
            $dateEndCondition = date(DATE_RFC3339, $dateEnd->getTimestamp());
        }
        return [$dateStartCondition,$dateEndCondition];
    }

    public static function ReaplaceCategoriesName(){
        $arr_cats = explode(",",Edadeal::$codeLisenArraySettings['name_cats']);
        if (!empty($arr_cats)) return $arr_cats;
        else return [];
    }
}