<?

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config as Conf;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Entity\Base;
use \Bitrix\Main\Application;

Loc::loadMessages(__FILE__);
Class ftden45_torgovie_marki extends CModule
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

        $this->MODULE_ID = 'ftden45.torgovie_marki';
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = Loc::getMessage("TORGOVIE_MARKI_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("TORGOVIE_MARKI_MODULE_DESC");

		$this->PARTNER_NAME = Loc::getMessage("TORGOVIE_MARKI_PARTNER_NAME");
		$this->PARTNER_URI = Loc::getMessage("TORGOVIE_MARKI_PARTNER_URI");

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

    public function installFiles() {
        // копируем js-файлы, необходимые для работы модуля
        CopyDirFiles(
            __DIR__.'/assets/scripts',
            Application::getDocumentRoot().'/bitrix/js/'.$this->MODULE_ID.'/',
            true,
            true
        );
        // копируем css-файлы, необходимые для работы модуля
        CopyDirFiles(
            __DIR__.'/assets/styles',
            Application::getDocumentRoot().'/bitrix/css/'.$this->MODULE_ID.'/',
            true,
            true
        );
    }

    public function UnInstallFiles() {
        // удаляем js-файлы
        Directory::deleteDirectory(
            Application::getDocumentRoot().'/bitrix/js/'.$this->MODULE_ID
        );
        // удаляем css-файлы
        Directory::deleteDirectory(
            Application::getDocumentRoot().'/bitrix/css/'.$this->MODULE_ID
        );
        // удаляем настройки нашего модуля
        Option::delete($this->MODULE_ID);
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
            $APPLICATION->ThrowException(Loc::getMessage("TORGOVIE_MARKI_INSTALL_ERROR_VERSION"));
        }
	}

	function DoUninstall()
	{
        global $APPLICATION;

        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();

        if($request["step"]<2)
        {
            $APPLICATION->IncludeAdminFile(Loc::getMessage("TORGOVIE_MARKI_UNINSTALL_TITLE"), $this->GetPath()."/install/unstep1.php");
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

            $APPLICATION->IncludeAdminFile(Loc::getMessage("TORGOVIE_MARKI_UNINSTALL_TITLE"), $this->GetPath()."/install/unstep2.php");
        }
	}

    function GetModuleRightList()
    {
        return array(
            "reference_id" => array("D","K","S","W"),
            "reference" => array(
                "[D] ".Loc::getMessage("TORGOVIE_MARKI_DENIED"),
                "[K] ".Loc::getMessage("TORGOVIE_MARKI_READ_COMPONENT"),
                "[S] ".Loc::getMessage("TORGOVIE_MARKI_WRITE_SETTINGS"),
                "[W] ".Loc::getMessage("TORGOVIE_MARKI_FULL"))
        );
    }
}
?>