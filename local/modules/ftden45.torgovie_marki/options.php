<?php

//namespace ;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Page\Asset;

\Bitrix\Main\Loader::includeModule('iblock');
Bitrix\Main\Loader::includeModule("sale");


$module_id = 'ftden45.torgovie_marki'; //обязательно, иначе права доступа не работают!

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
Loc::loadMessages(__FILE__);


\Bitrix\Main\Loader::includeModule($module_id);


$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();

function print_color ($item,$die = true) {
    echo "<pre>";
    echo "st_block";
    print_r($item);
    echo "</pre>";
    echo "end_";
    if ($die) die();
}

function getOptionsProduct (&$codeLisenArraySettings,$module_id)
{
    $codeLisenArraySettings['subject_email'] = \COption::GetOptionString($module_id, "subject_email", "");
    $codeLisenArraySettings['checkbox_category'] = \COption::GetOptionString($module_id, "checkbox_category", "");
    $codeLisenArraySettings['emails'] = \COption::GetOptionString($module_id, "emails", "");
    $codeLisenArraySettings['categories_product_ids'] = \COption::GetOptionString($module_id, "categories_product_ids", "");
    $codeLisenArraySettings['not_categories_product_ids'] = \COption::GetOptionString($module_id, "not_categories_product_ids", "");
    $codeLisenArraySettings['torgovie_marks_product_ids'] = \COption::GetOptionString($module_id, "torgovie_marks_product_ids", "");
    $codeLisenArraySettings['not_torgovie_marks_product_ids'] = \COption::GetOptionString($module_id, "not_torgovie_marks_product_ids", "");
}
function getCategories ($mod = 'get_cat')
{
    $iblockId = 114;

    $arFilter = Array("IBLOCK_ID"=>$iblockId, "ACTIVE"=>"Y");
    $resEl = \CIBlockSection::GetList(array('left_margin' => 'asc'),$arFilter);

    $currentUrl = (\CMain::IsHTTPS()) ? "https://" : "http://";
    $currentUrl .= $_SERVER["HTTP_HOST"];
    $catalogsInfo = [];
    $catalogsInfoWithChilds = [];
    $countProducts = 0;
    while($arEl = $resEl->GetNextElement()) {
        $countProducts++;
        $arFields = $arEl->GetFields();
        if (false) {
            echo $arFields['ID'].'<br>';
            echo $arFields['NAME'].'<br>';
            echo $arFields['DEPTH_LEVEL'].'<br>';
            echo "<pre>";
            print_r($arFields);
            echo "</pre>";
        }
        $space = '';
        if ($arFields['DEPTH_LEVEL'] > 0) {
            for ($i = 0; $i < $arFields['DEPTH_LEVEL'];$i++) {if ($i >= 1) $space .= '-';}
        }
        if ($mod == 'get_cat') {
            $catalogsInfo['ID_SELECT'][$arFields['ID'] . "_" . $arFields['DEPTH_LEVEL'] . "_" . $arFields['IBLOCK_SECTION_ID']] = $space . $arFields['NAME'];
            $catalogsInfo['ID'][$arFields['ID']] = $arFields['NAME'];
        }
        else if ($mod == 'get_cat_depth') {
            $catalogsInfo[$arFields['ID']]['NAME'] = $arFields['NAME'];
            $catalogsInfo[$arFields['ID']]['DEPTH_LEVEL'] = $arFields['DEPTH_LEVEL'];
            $catalogsInfo[$arFields['ID']]['IBLOCK_SECTION_ID'] = $arFields['IBLOCK_SECTION_ID'];
        }
    }
    if  ($mod == 'get_cat_depth') {
        foreach ($catalogsInfo as $idCat => $params) {
            if ($catalogsInfo[$idCat]['DEPTH_LEVEL'] == 1)
                foreach ($catalogsInfo as $idCatChild => $paramsChild) {
                    if ($idCat == $paramsChild['IBLOCK_SECTION_ID']) {
                        $catalogsInfoWithChilds[$idCat][] = $idCatChild;
                    }
                }
        }
        return [$catalogsInfoWithChilds,$catalogsInfo];
    }
    return $catalogsInfo;
}
function getTorgovieMarks()
{
    $iblockId = 118;

    $arSelect = ['ID', "IBLOCK_ID", 'NAME', 'DETAIL_PICTURE', 'DATE_ACTIVE_FROM', 'DATE_ACTIVE_TO', "PROPERTY_*"];
    $arFilter = Array("IBLOCK_ID"=>$iblockId, "ACTIVE"=>"Y");
    $resEl = \CIBlockElement::GetList(array('left_margin' => 'asc'),$arFilter);

    $currentUrl = (\CMain::IsHTTPS()) ? "https://" : "http://";
    $currentUrl .= $_SERVER["HTTP_HOST"];
    $torgovieMarki = [];
    while($arEl = $resEl->GetNextElement()) {
        $countProducts++;
        $arFields = $arEl->GetFields();
        $arProps = $arEl->GetProperties();
        if (false) {
            echo $arFields['ID'].'<br>';
            echo $arFields['NAME'].'<br>';
            echo "<pre>";
            echo "</pre>";
        }
        $torgovieMarki['ID_SELECT'][$arFields['ID']."_0_0"] = $arFields['NAME'];
        $torgovieMarki['ID'][$arFields['ID']] = $arFields['NAME'];

    }
    return $torgovieMarki;
}
function parserIdsAreatext ($textArea = 'Бытовая химия, хозтовары(1056)')
{
    $catalogs = getCategories('get_cat_depth');
    $childsIds = $catalogs[0];
    $allCatalogsIds = $catalogs[1];
    //echo "start: ".$textArea.'<br>';
    $idsString = explode(";", $textArea);
    $Ids = []; // чистые id категорий без текста(их названий)
    foreach ($idsString as $string) {
        if (preg_match('/\((.*?)\)/i',$string, $outArrayResult)) {
            // если есть в массиве то это большая категория, и добавляем все подкатегории
            // если нет то маленькая
            if (!empty($childsIds[$outArrayResult[1]]) &&  $allCatalogsIds[$outArrayResult[1]]['DEPTH_LEVEL'] == 1) {
                //print_r($childsIds);
                foreach ($childsIds[$outArrayResult[1]] as $id) {
                    $Ids[] = $id;
                }
            } else {
                $Ids[] = $outArrayResult[1];
            }
        }
    }
    return $Ids;
}
function parserIdsAreatextTorgMarks ($textArea)
{
    $idsString = explode(";", $textArea);
    $Ids = []; // чистые id категорий без текста(их названий)
    foreach ($idsString as $string) {
        if (preg_match('/\((.*?)\)/i',$string, $outArrayResult)) {
            $Ids[] = $outArrayResult[1];
        }
    }
    return $Ids;
}
function subArrayNotIds ($ids,$notIds)
{
    return array_diff($ids,$notIds);
}
function filterCats($codeLisenArraySettings) {
    $filterCategoriesTorgMaroks = [];
    $filterCategoriesTorgMaroks['cats_ids'] = parserIdsAreatext($codeLisenArraySettings['categories_product_ids']);
    $filterCategoriesTorgMaroks['torg_marks_ids'] = parserIdsAreatextTorgMarks($codeLisenArraySettings['torgovie_marks_product_ids']);
    $filterCategoriesTorgMaroks['not_cats_ids'] = parserIdsAreatext($codeLisenArraySettings['not_categories_product_ids']);
    $filterCategoriesTorgMaroks['not_torg_marks_ids'] = parserIdsAreatextTorgMarks($codeLisenArraySettings['not_torgovie_marks_product_ids']);
    $filterCategoriesTorgMaroks['cats_ids_unic'] = subArrayNotIds($filterCategoriesTorgMaroks['cats_ids'],$filterCategoriesTorgMaroks['not_cats_ids']);
    $filterCategoriesTorgMaroks['torg_marks_ids_unic'] = subArrayNotIds($filterCategoriesTorgMaroks['torg_marks_ids'],$filterCategoriesTorgMaroks['not_torg_marks_ids']);
    return $filterCategoriesTorgMaroks;
}

## выборка категорий
$catalogsInfo = [];
$catalogsInfo = getCategories();
## Выбор Торговой Марки
$torgovieMarki = [];
$torgovieMarki = getTorgovieMarks();
##

#Описание опций

//Asset::getInstance()->addJs('/bitrix/js/'.$module_id.'/scripts.js');

$options = ['modul_id' => $module_id, 'key1' => 'value1', 'key2' => 'value2'];
$options = json_encode($options);

Asset::getInstance()->addString(
    "<script id='".str_replace('.','-',$module_id)."-params' data-params='".$options."'></script>",
    true
);
Asset::getInstance()->addJs('/local/modules/'.$module_id.'/install/assets/scripts/scripts.js');

$aTabs = array(
    array(
        'DIV' => 'edit1',
        'TAB' => Loc::getMessage('TORGOVIE_MARKI_TAB_SETTINGS'),
        'OPTIONS' => array(
            array('info_user', Loc::getMessage('TORGOVIE_MARKI_FIELD_VALA'),
                'Grinkovav@sibkon.ru',
                array('text', 50)),
            array('subject_email', Loc::getMessage('TORGOVIE_MARKI_FIELD_SUBJECT_EMAIL'),
                'Тема письма',
                array('text', 50)),
            array('path', 'файл по пути: https://yarbox.ru//upload/order_excel_file/(ctategories.csv,ctategories_id.csv,torgovie_marki.csv,torgovie_marki_id.csv)',
                '',
                array('text')),
            array('emails', Loc::getMessage('TORGOVIE_MARKI_FIELD_TEXT_EMAILS'),
                'ftforest640@gmail.com,kolosovd@sibkon.ru',
                array('text', 50)),
            array('checkbox_category', Loc::getMessage('TORGOVIE_MARKI_FIELD_TRADEMARKS_FROM_CATEGORIES'),
                false,
                array('checkbox')),

            array('categories_product', Loc::getMessage('TORGOVIE_MARKI_FIELD_CHOSE_CATEGORY'),
                '',
                array('selectbox',$catalogsInfo['ID_SELECT'])),
            array('categories_product_ids', Loc::getMessage('TORGOVIE_MARKI_FIELD_CHOSE_CATEGORY_IDS'),
                '',
                array('textarea', 10, 50)),
            array('torgovie_marks_product', Loc::getMessage('TORGOVIE_MARKI_FIELD_CHOSE_TORGOVIE_MARKS'),
                '',
                array('selectbox',$torgovieMarki['ID_SELECT'])),
            array('torgovie_marks_product_ids', Loc::getMessage('TORGOVIE_MARKI_FIELD_CHOSE_TORGOVIE_MARKS_IDS'),
                '',
                array('textarea', 10, 50)),

            array('not_categories_product', Loc::getMessage('TORGOVIE_MARKI_FIELD_CHOSE_NOT_CATEGORY'),
                '',
                array('selectbox',$catalogsInfo['ID_SELECT'])),
            array('not_categories_product_ids', Loc::getMessage('TORGOVIE_MARKI_FIELD_CHOSE_NOT_CATEGORY_IDS'),
                '',
                array('textarea', 10, 50)),
            array('not_torgovie_marks_product', Loc::getMessage('TORGOVIE_MARKI_FIELD_CHOSE_NOT_TORGOVIE_MARKS'),
                '',
                array('selectbox',$torgovieMarki['ID_SELECT'])),
            array('not_torgovie_marks_product_ids', Loc::getMessage('TORGOVIE_MARKI_FIELD_CHOSE_NOT_TORGOVIE_MARKS_IDS'),
                '',
                array('textarea', 10, 50)),
        )
    ),
    array(
        "DIV" => "edit2",
        "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"),
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS")
    ),
);


#Сохранение

if ($request->isPost() && $request['Export'] && check_bitrix_sessid())
{

    foreach ($aTabs as $aTab)
    {
        //Или можно использовать __AdmSettingsSaveOptions($MODULE_ID, $arOptions);
        foreach ($aTab['OPTIONS'] as $arOption)
        {
            if (!is_array($arOption)) //Строка с подсветкой. Используется для разделения настроек в одной вкладке
                continue;

            if ($arOption['note']) //Уведомление с подсветкой
                continue;

            //Или __AdmSettingsSaveOption($MODULE_ID, $arOption);
            $optionName = $arOption[0];

            $optionValue = $request->getPost($optionName);

            Option::set($module_id, $optionName, is_array($optionValue) ? implode(",", $optionValue):$optionValue);
        }
    }
    // Mobile Code start ftden45


    $name_file_output = 'file.json';
    $codeLisenArraySettings = [];
    getOptionsProduct($codeLisenArraySettings, $module_id);
    $filtersCatTradeMarks = filterCats($codeLisenArraySettings);


    $arFilter = Array("IBLOCK_ID"=>114, "ACTIVE" => 'Y', 'IBLOCK_SECTION_ID' => $filtersCatTradeMarks['cats_ids_unic'], 'PROPERTY_1070' => $filtersCatTradeMarks['torg_marks_ids_unic']);
    $arSelect = ['ID', "IBLOCK_SECTION_ID", 'NAME', 'DETAIL_PICTURE', 'DATE_ACTIVE_FROM', 'DATE_ACTIVE_TO', "PROPERTY_*"];
    $resEl = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
    $counter = 0;
    $indexStart = 0;
    $index = 0;
    $ifId = false;
    $idProduct = 23699;

    $torgovieMarkiAll = [];
    $catalogsInfoAll = [];
    while($arEl = $resEl->GetNextElement()){
        $arFields = $arEl->GetFields();
        if (false) {
            echo "NAME ".$arFields['NAME']."<br>"; // имя товара
            echo "CATEGORY_ID ".$arFields['IBLOCK_SECTION_ID']."<br>"; // id категории
            echo "TORGOVAJAMARKA ".$arFields['PROPERTY_1070']."<br>"; // id торговой марки
        }
        if (true) {
            $torgovieMarkiAll[$arFields['PROPERTY_1070']][] = $arFields['IBLOCK_SECTION_ID'];
            $catalogsInfoAll[$arFields['IBLOCK_SECTION_ID']][] = $arFields['PROPERTY_1070'];
            continue;
        }
        if ($ifId && $arFields["ID"] != $idProduct) {
            continue;
        }
    }


    //$torgovieMarkiAll[$arFields['PROPERTY_1070']][] = $arFields['IBLOCK_SECTION_ID'];
    //$catalogsInfoAll[$arFields['IBLOCK_SECTION_ID']][] = $arFields['PROPERTY_1070'];

    // key это ID torgovoi Marki
    // value это ID category
    $torgovieMarkiAllCsv = [];
    $torgovieMarkiAllCsvWithId = [];
    $torgovieMarkiAllCsv[] = ['Торговая марка','Категории'];
    $torgovieMarkiAllCsvWithId[] = ['Торговая марка','Категории'];
    foreach ($torgovieMarkiAll as $key => $value) {
        $stringCategory = '';
        $stringCategoryId = '';
        $value = array_unique($value);
        foreach ($value as $el) {
            $stringCategory .= $catalogsInfo['ID'][$el].",";
            $stringCategoryId .= $catalogsInfo['ID'][$el]."(".$el."),";
        }
        $torgovieMarkiAllCsv[] = [$torgovieMarki['ID'][$key],$stringCategory];
        $torgovieMarkiAllCsvWithId[] = [$torgovieMarki['ID'][$key]."(".$key.")",$stringCategoryId];
    }

    $catalogsInfoAllCsv = [];
    $catalogsInfoAllCsvWithId = [];
    $catalogsInfoAllCsv[] = ['Категория','Торговые марка'];
    $catalogsInfoAllCsvWithId[] = ['Категория','Торговые марки'];
    foreach ($catalogsInfoAll as $key => $value) {
        $stringTorgovieMarki = '';
        $stringTorgovieMarkiId = '';
        $value = array_unique($value);
        foreach ($value as $el) {
            $stringTorgovieMarki .= $torgovieMarki['ID'][$el].",";
            $stringTorgovieMarkiId .= $torgovieMarki['ID'][$el]."(".$el."),";
        }
        $catalogsInfoAllCsv[] = [$catalogsInfo['ID'][$key],$stringTorgovieMarki];
        $catalogsInfoAllCsvWithId[] = [$catalogsInfo['ID'][$key]."(".$key.")",$stringTorgovieMarkiId];
    }

    //Приводим поля к формату который может прочитать 1с (т.к. стандартный формат csv она не понимает)
    function csvRFC1c7Field($fieldValue)
    {
        $res = $fieldValue;
        $res = trim($res);
        $res = str_replace(["\r", "\n"], '', $res);
        return $res;
    }
    //Приводим вывод к формату который может прочитать 1с (т.к. стандартный формат csv она не понимает)
    function csvRFC1c7Content($content)
    {
        $content = mb_convert_encoding($content, 'WINDOWS-1251', "utf-8");
        $content = str_replace("\n", "\r\n", $content);
        return $content;
    }
    function csvCreate ($itemsCsv = [], $name = 'file.csv') {

        $content = '';
        $handle = fopen('php://temp', 'r+');
        $header = array_keys($itemsCsv[0]);
        fputcsv($handle, $header, ';', '"');

        foreach ($itemsCsv as $line) {
            foreach ($line as $key => $value) {
                $line[$key] = csvRFC1c7Field($value);
            }
            fputcsv($handle, $line, ';', '"');
        }

        rewind($handle);
        while (!feof($handle)) {
            $content .= fread($handle, 8192);
        }
        fclose($handle);
        $content = csvRFC1c7Content($content);
        //return $content;
        $filePath = \Bitrix\Main\Application::getDocumentRoot().'/upload/trademarks/'.$name;
        $file = new \Bitrix\Main\IO\File($filePath);
        $file->putContents($content);
    }

    csvCreate($catalogsInfoAllCsv,'ctategories.csv');
    csvCreate($catalogsInfoAllCsvWithId,'ctategories_id.csv');
    if ($codeLisenArraySettings['checkbox_category']) {
        csvCreate($torgovieMarkiAllCsv,'torgovie_marki.csv');
        csvCreate($torgovieMarkiAllCsvWithId,'torgovie_marki_id.csv');
    }

    $path1 = '/home/v/vikuloet/sibshop.b2c/public_html/upload/trademarks/';
    $dateNow = date('d_m_Y_H_i_s');
    $fileName1 = 'ctategories.csv';
    $fileName2 = 'ctategories_id.csv';
    $fileName1New = 'ctategories_'.$dateNow.'.csv';
    $fileName2New = 'ctategories_id_'.$dateNow.'.csv';
    if ($codeLisenArraySettings['checkbox_category']) {
        $fileName3 = 'torgovie_marki.csv';
        $fileName4 = 'torgovie_marki_id.csv';
        $fileName3New = 'torgovie_marki_' . $dateNow . '.csv';
        $fileName4New = 'torgovie_marki_' . $dateNow . '.csv';
    }
    $dateNow = date('d.m.Y H:i:s');
    $subject = $codeLisenArraySettings['subject_email'];
    $emails = explode(",",$codeLisenArraySettings['emails']);
    $mail = new \PHPMailer\PHPMailer\PHPMailer;

    // Настройки вашей почты
    $mail->Host       = 'smtp.beget.com'; // SMTP сервера вашей почты
    $mail->Username   = 'report@yarbox.ru'; // Логин на почте
    $mail->Password   = 'Kolosov!159753'; // Пароль на почте
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;
    $mail->setFrom('report@yarbox.ru', 'Денис Колосов'); // Адрес самой почты и имя отправителя

    //$mail->setFrom('ftforest640@gmail.com', 'Денис Колосов');
    $mail->CharSet = "UTF-8"; // или др.
    foreach ($emails as $email) $mail->addAddress($email, 'User');
    $mail->Subject = $subject.": ".$dateNow;
    $mail->msgHTML(

        'Денис Колосов <a href="mailto:kolosovd@sibkon.ru?subject=Запрос новых Торговых Марок по категориям">kolosovd@sibkon.ru</a><br>'.
        "Дата создания файлов ".$dateNow
    );
    // Attach uploaded files
    $mail->addAttachment($path1.$fileName1,$fileName1New);
    $mail->addAttachment($path1.$fileName2,$fileName2New);
    if ($codeLisenArraySettings['checkbox_category']) {
        $mail->addAttachment($path1 . $fileName3, $fileName3New);
        $mail->addAttachment($path1 . $fileName4, $fileName4New);
    }
    //$mail->addAttachment($filename2);
    //$r = $mail->send();
    if(!$mail->send()) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        echo 'Message has been sent';
    }




    //$content = str_replace("\n", "\r\n", $content);



    /*$csvFile->LoadFile(\Bitrix\Main\Application::getDocumentRoot().'/upload/order_excel_file/file.csv');
    $csvFile->SetDelimiter(',');
    while ($arRes = $csvFile->Fetch()) {
        echo($arRes);
    }*/

    // Mobile Code end ftden45

}

#Визуальный вывод

$tabControl = new CAdminTabControl('tabControl', $aTabs);

?>
<? $tabControl->Begin(); ?>
<form method='post' action='<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($request['mid'])?>&amp;lang=<?=$request['lang']?>' name='torgovie_marki_settings'>

    <? foreach ($aTabs as $aTab):
            if($aTab['OPTIONS']):?>
        <? $tabControl->BeginNextTab(); ?>
        <? __AdmSettingsDrawList($module_id, $aTab['OPTIONS']); ?>

    <?      endif;
        endforeach; ?>

    <?
    $tabControl->BeginNextTab();



    $tabControl->Buttons(); ?>

    <div class="notes" id="notes">
    <input type="submit" name="SaveParames" value="<?echo Loc::getMessage('TORGOVIE_MARKI_EXPORT_EDADEAL')?>">
    <input type="submit" name="Export" value="<?echo Loc::getMessage('TORGOVIE_MARKI_EXPORT_EDADEAL')?>">
    <input type="reset" name="reset" value="<?echo GetMessage('MAIN_RESET')?>" onclick="work_with_row()">
    <?=bitrix_sessid_post();?>
</form>
<script type="text/javascript">
    function work_with_row(){
        alert('zzzzzz');
    }
</script>
<? $tabControl->End(); ?>

