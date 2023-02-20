<?php
//ini_set('memory_limit', '500M');
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\IO;
use Bitrix\Main\Application;

use EdadealExportFile\Edadeal;


use Bitrix\Main,
    Bitrix\Catalog;


\Bitrix\Main\Loader::includeModule('iblock');


$module_id = 'custom.edadealexport'; //обязательно, иначе права доступа не работают!

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
Loc::loadMessages(__FILE__);


\Bitrix\Main\Loader::includeModule($module_id);


$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();

#Описание опций

$aTabs = array(
    array(
        'DIV' => 'edit1',
        'TAB' => Loc::getMessage('FTDEN45_EDADEALEXPJSON_TAB_SETTINGS'),
        'OPTIONS' => array(
            array('field_name_file_output', Loc::getMessage('FTDEN45_EDADEALEXPJSON_FIELD_NAME_FILE_EXPORT'),
                '',
                array('text', 50)),
            array('date_end_conditions', Loc::getMessage('FTDEN45_EDADEALEXPJSON_DATA_END_CONDITIONS'),
                date("d.m.Y"),
                array('text', 50)),
            array('torgovie_mark', Loc::getMessage('FTDEN45_EDADEALEXPJSON_TORGOVAJA_MARKA'),
                '1395,1471',
                array('text', 50)),
            array('text_replace', Loc::getMessage('FTDEN45_EDADEALEXPJSON_TEXT_REPLACE'),
                'Акция действует при наличии товара в магазине. Количество товара ограничено.',
                array('text', 50)),
            array('name_cats', Loc::getMessage('FTDEN45_EDADEALEXPJSON_NAME_CATS'),
                'Вафли',
                array('textarea', 10, 50)),
            /*array('field_text', Loc::getMessage('FTDEN45_EDADEALEXPJSON_FIELD_TEXT_TITLE'),
                '',
                array('textarea', 10, 50)),
            array('field_list', Loc::getMessage('FTDEN45_EDADEALEXPJSON_FIELD_LIST_TITLE'),
                '',
                array('multiselectbox',array('var1'=>'var1','var2'=>'var2','var3'=>'var3','var4'=>'var4'))),*/
        )
    ),
    array(
        "DIV" => "edit2",
        "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"),
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS")
    ),
);
#Сохранение

$name_file_output = 'file.json';
$edadeal_settings = [];
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

            if ($optionName == 'field_name_file_output') $edadeal_settings['field_name_file_output'] = $optionValue = $request->getPost($optionName);
            else if ($optionName == 'date_end_conditions') $edadeal_settings['date_end_conditions'] = $optionValue = $request->getPost($optionName);
            else if ($optionName == 'torgovie_mark') $edadeal_settings['torgovie_mark'] = $optionValue = $request->getPost($optionName);
            else $optionValue = $request->getPost($optionName);

            Option::set($module_id, $optionName, is_array($optionValue) ? implode(",", $optionValue):$optionValue);
        }
    }

    \EdadealExportFile\Edadeal::CreateEdadealFile();

}

#Визуальный вывод

$tabControl = new CAdminTabControl('tabControl', $aTabs);

?>
<? $tabControl->Begin(); ?>
<form method='post' action='<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($request['mid'])?>&amp;lang=<?=$request['lang']?>' name='ftden45_edadealexpjson_settings'>

    <? foreach ($aTabs as $aTab):
            if($aTab['OPTIONS']):?>
        <? $tabControl->BeginNextTab(); ?>
        <? __AdmSettingsDrawList($module_id, $aTab['OPTIONS']); ?>

    <?      endif;
        endforeach; ?>

    <?
    $tabControl->BeginNextTab();



    $tabControl->Buttons(); ?>

    <input type="submit" name="Export" value="<?echo Loc::getMessage('FTDEN45_EDADEALEXPJSON_EXPORT_EDADEAL')?>">
    <input type="reset" name="reset" value="<?echo GetMessage('MAIN_RESET')?>">
    <?=bitrix_sessid_post();?>
</form>
<? $tabControl->End(); ?>
