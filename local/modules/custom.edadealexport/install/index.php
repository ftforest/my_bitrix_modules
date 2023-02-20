<?

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config as Conf;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Entity\Base;
use \Bitrix\Main\Application;

Loc::loadMessages(__FILE__);
Class custom_edadealexport extends CModule
{
    var $exclusionAdminFiles;

	function __construct()
	{
		$arModuleVersion = array();
		include(__DIR__."/version.php");

        $this->exclusionAdminFiles=array(
            '..',
            '.',
            'menu.php',
            'operation_description.php',
            'task_description.php'
        );

        $this->MODULE_ID = 'custom.edadealexport';
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = Loc::getMessage("FTDEN45_EDADEALEXPJSON_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("FTDEN45_EDADEALEXPJSON_MODULE_DESC");

		$this->PARTNER_NAME = Loc::getMessage("FTDEN45_EDADEALEXPJSON_PARTNER_NAME");
		$this->PARTNER_URI = Loc::getMessage("FTDEN45_EDADEALEXPJSON_PARTNER_URI");

        $this->MODULE_SORT = 1;
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS='Y';
        $this->MODULE_GROUP_RIGHTS = "Y";
	}

    //Определяем место размещения модуля
    public function GetPath($notDocumentRoot=false)
    {
        if($notDocumentRoot)
            return str_ireplace(Application::getDocumentRoot(),'',dirname(__DIR__));
        else
            return dirname(__DIR__);
    }

    //Проверяем что система поддерживает D7
    public function isVersionD7()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '14.00.00');
    }

    function InstallDB()
    {
        Loader::includeModule($this->MODULE_ID);
        return true;
    }

    function UnInstallDB()
    {
        Loader::includeModule($this->MODULE_ID);
        Option::delete($this->MODULE_ID);
        return true;
    }

	function InstallEvents()
    {
        return true;
    }

	function UnInstallEvents()
	{
        return true;
	}

	function InstallFiles($arParams = array())
	{
        return true;
	}

	function UnInstallFiles()
	{
        return true;
	}

	function DoInstall()
	{
        global $APPLICATION;
        if($this->isVersionD7())
        {
            $this->InstallDB();
            $this->InstallEvents();
            $this->InstallFiles();

            \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
        }
        else
        {
            $APPLICATION->ThrowException(Loc::getMessage("FTDEN45_EDADEALEXPJSON_INSTALL_ERROR_VERSION"));
        }
	}

	function DoUninstall()
	{
        global $APPLICATION;

        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();

        if($request["step"]<2)
        {
            $APPLICATION->IncludeAdminFile(Loc::getMessage("FTDEN45_EDADEALEXPJSON_UNINSTALL_TITLE"), $this->GetPath()."/install/unstep1.php");
        }
        elseif($request["step"]==2)
        {
            $this->UnInstallFiles();
			$this->UnInstallEvents();

            //if($request["savedata"] != "Y")
                $this->UnInstallDB();

            \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);

            #работа с .settings.php
            /*$configuration = Conf\Configuration::getInstance();
            $academy_module_d7=$configuration->get('academy_module_d7');
            $academy_module_d7['uninstall']=$academy_module_d7['uninstall']+1;
            $configuration->add('academy_module_d7', $academy_module_d7);
            $configuration->saveConfiguration();*/
            #работа с .settings.php

            $APPLICATION->IncludeAdminFile(Loc::getMessage("FTDEN45_EDADEALEXPJSON_UNINSTALL_TITLE"), $this->GetPath()."/install/unstep2.php");
        }
	}

    function GetModuleRightList()
    {
        return array(
            "reference_id" => array("D","K","S","W"),
            "reference" => array(
                "[D] ".Loc::getMessage("FTDEN45_EDADEALEXPJSON_DENIED"),
                "[K] ".Loc::getMessage("FTDEN45_EDADEALEXPJSON_READ_COMPONENT"),
                "[S] ".Loc::getMessage("FTDEN45_EDADEALEXPJSON_WRITE_SETTINGS"),
                "[W] ".Loc::getMessage("FTDEN45_EDADEALEXPJSON_FULL"))
        );
    }
}
?>