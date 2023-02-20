<?php
/**
 * Eaddeal file json
 *
 * запуск - CRON, запускается в 11:00 и в 23:00
 */

$_SERVER['DOCUMENT_ROOT']   =   str_replace("local".DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."custom.edadealexport".DIRECTORY_SEPARATOR."cron",'',__DIR__);

require_once($_SERVER['DOCUMENT_ROOT']."".DIRECTORY_SEPARATOR."bitrix".DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."main".DIRECTORY_SEPARATOR."include".DIRECTORY_SEPARATOR."prolog_before.php");

\Bitrix\Main\Loader::includeModule("custom.edadealexport");
\EdadealExportFile\Edadeal::CreateEdadealFile();

require_once($_SERVER['DOCUMENT_ROOT']."".DIRECTORY_SEPARATOR."bitrix".DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."main".DIRECTORY_SEPARATOR."include".DIRECTORY_SEPARATOR."epilog_after.php");