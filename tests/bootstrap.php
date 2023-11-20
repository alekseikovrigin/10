<?php
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

$moduleId = basename(dirname(__DIR__));

const NOT_CHECK_PERMISSIONS = true;
const NO_AGENT_CHECK = true;
$GLOBALS['DBType'] = 'mysql';
$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../../../..');
$_SERVER["DOCUMENT_ROOT"] = __DIR__ . '/../../../../';

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

$_SESSION['SESS_AUTH']['USER_ID'] = 1;

require_once($_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php');

try {
    Loader::includeModule($moduleId);
} catch (LoaderException $e) {
}

require_once('BitrixTestCase.php');
