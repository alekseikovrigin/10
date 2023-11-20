<?php

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;


require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/wizard.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/wizard_sol/utils.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/classes/mysql/cml2.php");

Loc::loadMessages(__FILE__);

class inetris_nocode extends CModule
{
    public $VENDOR = "inetris";
    public $MODULE_ID = "inetris.nocode";

    public $IBLOCK_TYPE = "nocode";
    public $baseXmlPath;

    public function __construct()
    {
        $arModuleVersion = array();

        include __DIR__ . '/version.php';
        $this->baseXmlPath = dirname(__FILE__) . "/xml/";

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_ID = "inetris.nocode";
        $this->MODULE_NAME = Loc::getMessage('INETRIS_NOCODE_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('INETRIS_NOCODE_MODULE_DESCRIPTION');
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->PARTNER_NAME = Loc::getMessage('INETRIS_NOCODE_MODULE_PARTNER_NAME');
        $this->PARTNER_URI = 'https://inetris.ru';
    }

    public function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->InstallFiles();

        $this->CreateIblocks();
    }

    public function DoUninstall()
    {
        $this->DelIblocks();

        $this->UnInstallFiles();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    function InstallFiles(): bool
    {
        if (Directory::isDirectoryExists($path = $this->GetPath() . "/install")) {
            CopyDirFiles(
                $this->GetPath()."/install/local/components/{$this->VENDOR}/news.list/",
                $_SERVER["DOCUMENT_ROOT"] . "/local/components/{$this->VENDOR}/news.list/",
                true, true);

            CopyDirFiles(
                $this->GetPath()."/install/site/",
                $_SERVER["DOCUMENT_ROOT"],
                true, true);

            return true;
        } else {
            throw new InvalidPathException($path);
            return true;
        }
    }

    function UnInstallFiles(): bool
    {
        if (Directory::isDirectoryExists($path = $this->GetPath() . "/install")) {
            $nocodeDemoDir = "/nocode_demo";

            Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . "/local/components/{$this->VENDOR}/news.list/");

            $path = Application::getDocumentRoot() . $nocodeDemoDir . "/index.php";
            File::isFileExists($path);
            File::deleteFile($path);

            Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . $nocodeDemoDir);

            return true;
        } else {
            throw new InvalidPathException($path);
            return true;
        }
    }

    public function GetPath($notDocumentRoot = false)
    {
        if($notDocumentRoot)
            return str_ireplace(Application::getDocumentRoot(),'',dirname(__DIR__));
        else {
            return dirname(__DIR__);
        }
    }

    function DelIblocks()
    {
        global $DB;
        CModule::IncludeModule("iblock");

        $DB->StartTransaction();
        if (!CIBlockType::Delete($this->IBLOCK_TYPE)){
            $DB->Rollback();
            CAdminMessage::ShowMessage(Array(
                "TYPE" => "ERROR",
                "MESSAGE" => GetMessage("NOCODE_IBLOCK_TYPE_DELETE_ERROR"),
                "DETAILS" => "",
                "HTML" => true
            ));
        }
        $DB->Commit();
    }

    public function CreateIblocks()
    {
        $arFieldsForType = array(
            'ID' => $this->IBLOCK_TYPE,
            'SECTIONS' => 'Y',
            'IN_RSS' => 'N',
            'SORT' => 500,
            'LANG' => array(
                'en' => array(
                    'NAME' => 'nocode',
                ),
                'ru' => array(
                    'NAME' => Loc::getMessage("NOCODE_IBLOCK_TYPE_NAME"),
                )
            )
        );

        if ($this->AddIblockType($arFieldsForType)) {

            $arFieldsForIblockRules = array(
                "ACTIVE" => "Y",
                "NAME" => Loc::getMessage("NOCODE_RULES_IBLOCK_NAME"),
                "CODE" => "nocode_rules",
                "XML_ID" => "nocode_rules",
                "IBLOCK_TYPE_ID" => $arFieldsForType["ID"],
                "SITE_ID" => "s1",
                "WORKFLOW" => "N",
                "BIZPROC" => "N",
                "GROUP_ID" => array("2" => "R"),
            );

            if ($iblockIDRules = $this->AddIblock($arFieldsForIblockRules)) {

            } else {
                CAdminMessage::ShowMessage(array(
                    "TYPE" => "ERROR",
                    "MESSAGE" => Loc::getMessage("NOCODE_RULES_IBLOCK_NOT_INSTALLED"),
                    "DETAILS" => "",
                    "HTML" => true
                ));
            }

            if ($this->AddContent("nocode_rules") == 0){
                CAdminMessage::ShowMessage(array(
                    "TYPE" => "ERROR",
                    "MESSAGE" => Loc::getMessage("NOCODE_RULES_CONTENT_NOT_INSTALLED"),
                    "DETAILS" => "",
                    "HTML" => true
                ));
            }

            $arFieldsForIblockTypes = array(
                "ACTIVE" => "Y",
                "NAME" => Loc::getMessage("NOCODE_TYPES_IBLOCK_NAME"),
                "CODE" => "nocode_types",
                "XML_ID" => "nocode_types",
                "IBLOCK_TYPE_ID" => $arFieldsForType["ID"],
                "SITE_ID" => "s1",
                "WORKFLOW" => "N",
                "BIZPROC" => "N",
                "GROUP_ID" => array("2" => "R")
            );
            $iblockIDTypes = $this->AddIblock($arFieldsForIblockTypes);

            if ($this->AddContent("nocode_types") == 0){
                CAdminMessage::ShowMessage(array(
                    "TYPE" => "ERROR",
                    "MESSAGE" => Loc::getMessage("NOCODE_TYPES_CONTENT_NOT_INSTALLED"),
                    "DETAILS" => "",
                    "HTML" => true
                ));
            }

            $arFieldsForIblockOper = array(
                "ACTIVE" => "Y",
                "NAME" => Loc::getMessage("NOCODE_OPER_IBLOCK_NAME"),
                "CODE" => "nocode_operations",
                "XML_ID" => "nocode_operations",
                "IBLOCK_TYPE_ID" => $arFieldsForType["ID"],
                "SITE_ID" => "s1",
                "WORKFLOW" => "N",
                "BIZPROC" => "N",
                "GROUP_ID" => array("2" => "R")
            );

            if ($iblockIDOper = $this->AddIblock($arFieldsForIblockOper)) {
                $arFieldsProp = [];
                $arFieldsProp[] = array(
                    "NAME" => Loc::getMessage("NOCODE_OPER_IBLOCK_PROP_OPER"),
                    "ACTIVE" => "Y",
                    "SORT" => "500",
                    "MULTIPLE" => "N",
                    "CODE" => "OPER",
                    "XML_ID" => "OPER",
                    "PROPERTY_TYPE" => "S",
                    "IBLOCK_ID" => $iblockIDOper
                );

                $arFieldsProp[] = array(
                    "NAME" => Loc::getMessage("NOCODE_OPER_IBLOCK_PROP_PREF"),
                    "ACTIVE" => "Y",
                    "SORT" => "500",
                    "MULTIPLE" => "N",
                    "CODE" => "PREF",
                    "XML_ID" => "PREF",
                    "PROPERTY_TYPE" => "S",
                    "IBLOCK_ID" => $iblockIDOper
                );

                $arFieldsProp[] = array(
                    "NAME" => Loc::getMessage("NOCODE_OPER_IBLOCK_PROP_OPERNOT"),
                    "ACTIVE" => "Y",
                    "SORT" => "500",
                    "MULTIPLE" => "N",
                    "CODE" => "OPERNOT",
                    "XML_ID" => "OPERNOT",
                    "PROPERTY_TYPE" => "S",
                    "IBLOCK_ID" => $iblockIDOper
                );

                $arFieldsProp[] = array(
                    "NAME" => Loc::getMessage("NOCODE_OPER_IBLOCK_PROP_PREFNOT"),
                    "ACTIVE" => "Y",
                    "SORT" => "500",
                    "MULTIPLE" => "N",
                    "CODE" => "PREFNOT",
                    "XML_ID" => "PREFNOT",
                    "PROPERTY_TYPE" => "S",
                    "IBLOCK_ID" => $iblockIDOper
                );

                $arFieldsProp[] = array(
                    "NAME" => Loc::getMessage("NOCODE_OPER_IBLOCK_PROP_OP_TYPE"),
                    "ACTIVE" => "Y",
                    "SORT" => "500",
                    "MULTIPLE" => "Y",
                    "CODE" => "OP_TYPE",
                    "XML_ID" => "OP_TYPE",
                    "PROPERTY_TYPE" => "E",
                    "LINK_IBLOCK_ID" => $iblockIDTypes,
                    "IBLOCK_ID" => $iblockIDOper
                );

                $arFields[0] = Array(
                    "VALUE" => Loc::getMessage("NOCODE_OPER_IBLOCK_PROP_PROCESS_STRONG"),
                    "DEF" => "N",
                    "XML_ID" => "strong",
                    "SORT" => "500"
                );
                $arFields[1] = Array(
                    "VALUE" => Loc::getMessage("NOCODE_OPER_IBLOCK_PROP_PROCESS_SEARCH"),
                    "DEF" => "N",
                    "XML_ID" => "search",
                    "SORT" => "500"
                );

                $arFieldsProp[] = array(
                    "NAME" => Loc::getMessage("NOCODE_OPER_IBLOCK_PROP_PROCESS"),
                    "ACTIVE" => "Y",
                    "SORT" => "500",
                    "MULTIPLE" => "N",
                    "CODE" => "PROCESS",
                    "XML_ID" => "PROCESS",
                    "PROPERTY_TYPE" => "L",
                    "VALUES" => $arFields,
                    "IBLOCK_ID" => $iblockIDOper
                );

                foreach ($arFieldsProp as $k=>$prop) {
                    $ibp = new \CIBlockProperty;
                    $propID = $ibp->Add($arFieldsProp[$k]);

                    if ($propID === "false"){
                        CAdminMessage::ShowMessage(array(
                            "TYPE" => "ERROR",
                            "MESSAGE" => Loc::getMessage("NOCODE_PROP_NOT_INSTALLED") . $arFieldsProp[$k]["CODE"],
                            "DETAILS" => "",
                            "HTML" => true
                        ));
                    }
                }

                if ($this->AddContent("nocode_operations") == 0){
                    CAdminMessage::ShowMessage(array(
                        "TYPE" => "ERROR",
                        "MESSAGE" => Loc::getMessage("NOCODE_OPER_CONTENT_NOT_INSTALLED"),
                        "DETAILS" => "",
                        "HTML" => true
                    ));
                }

            } else {
                CAdminMessage::ShowMessage(array(
                    "TYPE" => "ERROR",
                    "MESSAGE" => Loc::getMessage("NOCODE_OPER_IBLOCK_NOT_INSTALLED"),
                    "DETAILS" => "",
                    "HTML" => true
                ));
            }

            $arFieldsForIblockCrit = array(
                "ACTIVE" => "Y",
                "NAME" => Loc::getMessage("NOCODE_CRIT_IBLOCK_NAME"),
                "CODE" => "nocode_criterions",
                "XML_ID" => "nocode_criterions",
                "IBLOCK_TYPE_ID" => $arFieldsForType["ID"],
                "SITE_ID" => "s1",
                "WORKFLOW" => "N",
                "BIZPROC" => "N",
                "GROUP_ID" => array("2" => "R")
            );

            if ($iblockIDCrit = $this->AddIblock($arFieldsForIblockCrit)) {
                $arFieldsProp = [];
                $arFieldsProp[] = array(
                    "NAME" => Loc::getMessage("NOCODE_CRIT_IBLOCK_PROP_CODE_IN"),
                    "ACTIVE" => "Y",
                    "SORT" => "500",
                    "MULTIPLE" => "N",
                    "CODE" => "CODE_IN",
                    "XML_ID" => "CODE_IN",
                    "PROPERTY_TYPE" => "S",
                    "IBLOCK_ID" => $iblockIDCrit
                );

                $arFieldsProp[] = array(
                    "NAME" => Loc::getMessage("NOCODE_CRIT_IBLOCK_PROP_CODE_OUT"),
                    "ACTIVE" => "Y",
                    "SORT" => "500",
                    "MULTIPLE" => "N",
                    "CODE" => "CODE_OUT",
                    "XML_ID" => "CODE_OUT",
                    "PROPERTY_TYPE" => "S",
                    "IBLOCK_ID" => $iblockIDCrit
                );

                $arFieldsProp[] = array(
                    "NAME" => Loc::getMessage("NOCODE_CRIT_IBLOCK_PROP_HANDLER"),
                    "ACTIVE" => "Y",
                    "SORT" => "500",
                    "MULTIPLE" => "N",
                    "CODE" => "HANDLER",
                    "XML_ID" => "HANDLER",
                    "PROPERTY_TYPE" => "S",
                    "IBLOCK_ID" => $iblockIDCrit
                );

                $arFieldsProp[] = array(
                    "NAME" => Loc::getMessage("NOCODE_CRIT_IBLOCK_PROP_TYPE"),
                    "ACTIVE" => "Y",
                    "SORT" => "500",
                    "MULTIPLE" => "N",
                    "CODE" => "TYPE",
                    "XML_ID" => "TYPE",
                    "PROPERTY_TYPE" => "E",
                    "LINK_IBLOCK_ID" => $iblockIDTypes,
                    "IBLOCK_ID" => $iblockIDCrit
                );

                foreach ($arFieldsProp as $k=>$prop) {
                    $ibp = new \CIBlockProperty;
                    $propID = $ibp->Add($arFieldsProp[$k]);

                    if ($propID === "false"){
                        CAdminMessage::ShowMessage(array(
                            "TYPE" => "ERROR",
                            "MESSAGE" => Loc::getMessage("NOCODE_PROP_NOT_INSTALLED") . $arFieldsProp[$k]["CODE"],
                            "DETAILS" => "",
                            "HTML" => true
                        ));
                    }
                }

                if ($this->AddContent("nocode_criterions") == 0){
                    CAdminMessage::ShowMessage(array(
                        "TYPE" => "ERROR",
                        "MESSAGE" => Loc::getMessage("NOCODE_CRIT_CONTENT_NOT_INSTALLED"),
                        "DETAILS" => "",
                        "HTML" => true
                    ));
                }

            } else {
                CAdminMessage::ShowMessage(array(
                    "TYPE" => "ERROR",
                    "MESSAGE" => Loc::getMessage("NOCODE_CRIT_IBLOCK_NOT_INSTALLED"),
                    "DETAILS" => "",
                    "HTML" => true
                ));
            }

            $arFieldsForIblockCond = array(
                "ACTIVE" => "Y",
                "NAME" => Loc::getMessage("NOCODE_COND_IBLOCK_NAME"),
                "CODE" => "nocode_conditions",
                "XML_ID" => "nocode_conditions",
                "WORKFLOW" => "N",
                "BIZPROC" => "N",
                "IBLOCK_TYPE_ID" => $arFieldsForType["ID"],
                "SITE_ID" => "s1",
                "VERSION" => 2,
                "GROUP_ID" => array("2" => "R")
            );

            if ($iblockIDCond = $this->AddIblock($arFieldsForIblockCond)) {
                $arFieldsProp = [];
                $arFieldsProp[] = array(
                    "NAME" => Loc::getMessage("NOCODE_COND_IBLOCK_PROP_VALU"),
                    "ACTIVE" => "Y",
                    "SORT" => "400",
                    "MULTIPLE" => "N",
                    "CODE" => "VALU",
                    "XML_ID" => "VALU",
                    "PROPERTY_TYPE" => "S",
                    "IBLOCK_ID" => $iblockIDCond
                );

                $arFieldsProp[] = array(
                    "NAME" => Loc::getMessage("NOCODE_COND_IBLOCK_PROP_SUBORD"),
                    "ACTIVE" => "Y",
                    "SORT" => "800",
                    "MULTIPLE" => "N",
                    "CODE" => "SUBORDER",
                    "XML_ID" => "SUBORDER",
                    "PROPERTY_TYPE" => "E",
                    "LINK_IBLOCK_ID" => $iblockIDCond,
                    "IBLOCK_ID" => $iblockIDCond
                );

                $arFieldsProp[] = array(
                    "NAME" => Loc::getMessage("NOCODE_COND_IBLOCK_PROP_CRIT"),
                    "ACTIVE" => "Y",
                    "SORT" => "200",
                    "MULTIPLE" => "N",
                    "CODE" => "CRIT",
                    "XML_ID" => "CRIT",
                    "PROPERTY_TYPE" => "E",
                    "LINK_IBLOCK_ID" => $iblockIDCrit,
                    "IBLOCK_ID" => $iblockIDCond
                );

                $arFieldsProp[] = array(
                    "NAME" => Loc::getMessage("NOCODE_COND_IBLOCK_PROP_OPER"),
                    "ACTIVE" => "Y",
                    "SORT" => "300",
                    "MULTIPLE" => "N",
                    "CODE" => "OPER",
                    "XML_ID" => "OPER",
                    "PROPERTY_TYPE" => "E",
                    "LINK_IBLOCK_ID" => $iblockIDOper,
                    "IBLOCK_ID" => $iblockIDCond
                );

                $arFieldsProp[] = array(
                    "NAME" => Loc::getMessage("NOCODE_COND_IBLOCK_PROP_LINK"),
                    "ACTIVE" => "Y",
                    "SORT" => "900",
                    "MULTIPLE" => "N",
                    "CODE" => "LINK",
                    "XML_ID" => "LINK",
                    "PROPERTY_TYPE" => "E",
                    "LINK_IBLOCK_ID" => $iblockIDRules,
                    "IBLOCK_ID" => $iblockIDCond
                );

                $arFields[0] = Array(
                    "VALUE" => Loc::getMessage("NOCODE_COND_IBLOCK_PROP_TYPE_SORT"),
                    "DEF" => "N",
                    "XML_ID" => "sort",
                    "SORT" => "500"
                );
                $arFields[1] = Array(
                    "VALUE" => Loc::getMessage("NOCODE_COND_IBLOCK_PROP_TYPE_IN"),
                    "DEF" => "N",
                    "XML_ID" => "in",
                    "SORT" => "500"
                );
                $arFields[2] = Array(
                    "VALUE" => Loc::getMessage("NOCODE_COND_IBLOCK_PROP_TYPE_OUT"),
                    "DEF" => "N",
                    "XML_ID" => "out",
                    "SORT" => "500"
                );
                $arFields[3] = Array(
                    "VALUE" => Loc::getMessage("NOCODE_COND_IBLOCK_PROP_TYPE_LIMIT"),
                    "DEF" => "N",
                    "XML_ID" => "limit",
                    "SORT" => "500"
                );
                $arFields[4] = Array(
                    "VALUE" => Loc::getMessage("NOCODE_COND_IBLOCK_PROP_TYPE_LOGIC"),
                    "DEF" => "N",
                    "XML_ID" => "logic",
                    "SORT" => "500"
                );
                $arFields[5] = Array(
                    "VALUE" => Loc::getMessage("NOCODE_COND_IBLOCK_PROP_TYPE_PRIORITY"),
                    "DEF" => "N",
                    "XML_ID" => "priority",
                    "SORT" => "500"
                );

                $arFieldsProp[] = array(
                    "NAME" => Loc::getMessage("NOCODE_COND_IBLOCK_PROP_TYPE"),
                    "ACTIVE" => "Y",
                    "SORT" => "100",
                    "MULTIPLE" => "N",
                    "CODE" => "TYPE",
                    "XML_ID" => "TYPE",
                    "PROPERTY_TYPE" => "L",
                    "VALUES" => $arFields,
                    "IBLOCK_ID" => $iblockIDCond
                );

                foreach ($arFieldsProp as $k=>$prop) {
                    $ibp = new \CIBlockProperty;
                    $propID = $ibp->Add($arFieldsProp[$k]);

                    if ($propID === "false"){
                        CAdminMessage::ShowMessage(array(
                            "TYPE" => "ERROR",
                            "MESSAGE" => Loc::getMessage("NOCODE_PROP_NOT_INSTALLED") . $arFieldsProp[$k]["CODE"],
                            "DETAILS" => "",
                            "HTML" => true
                        ));
                    }
                }

                if ($this->AddContent("nocode_conditions") == 0){
                    CAdminMessage::ShowMessage(array(
                        "TYPE" => "ERROR",
                        "MESSAGE" => Loc::getMessage("NOCODE_COND_CONTENT_NOT_INSTALLED"),
                        "DETAILS" => "",
                        "HTML" => true
                    ));
                }

            } else {
                CAdminMessage::ShowMessage(array(
                    "TYPE" => "ERROR",
                    "MESSAGE" => Loc::getMessage("NOCODE_COND_IBLOCK_NOT_INSTALLED"),
                    "DETAILS" => "",
                    "HTML" => true
                ));
            }



        } else {
            CAdminMessage::ShowMessage(array(
                "TYPE" => "ERROR",
                "MESSAGE" => Loc::getMessage("NOCODE_IBLOCK_TYPE_NOT_INSTALLED"),
                "DETAILS" => "",
                "HTML" => true
            ));
        }
    }

    function AddIblockType($arFieldsIBT){
        global $DB;
        CModule::IncludeModule("iblock");

        $iblockType = $arFieldsIBT["ID"];

        $db_iblock_type = CIBlockType::GetList(Array("SORT" => "ASC"), Array("ID" => $iblockType));

        if (!$ar_iblock_type = $db_iblock_type->Fetch()){
            $obBlocktype = new CIBlockType;
            $DB->StartTransaction();
            $resIBT = $obBlocktype->Add($arFieldsIBT);
            if (!$resIBT){
                $DB->Rollback();
                echo 'Error: ' . $obBlocktype->LAST_ERROR . '';
                die();
            }else{
                $DB->Commit();
            }
        }else{
            return false;
        }

        return $iblockType;
    }

    function AddIblock($arFieldsIB){

        $iblockCode = $arFieldsIB["CODE"];
        $iblockType = $arFieldsIB["TYPE"];

        $ib = new CIBlock;

        $resIBE = CIBlock::GetList(Array(), Array('TYPE' => $iblockType, "CODE" => $iblockCode));
        if ($ar_resIBE = $resIBE->Fetch()){
            return false;
        }else{
            $ID = $ib->Add($arFieldsIB);
            $iblockID = $ID;
        }

        return $iblockID;
    }

    function AddProp($arFieldsProp){

        $ibp = new CIBlockProperty;
        $propID = $ibp->Add($arFieldsProp);

        return $propID;
    }

    function AddContent($filename){
        return ImportXMLFile(
            $this->baseXmlPath . $filename . ".xml",
            $this->IBLOCK_TYPE,
            "s1",
            $section_action = "N",
            $element_action = "N",
            $use_crc = true,
            $preview = false,
            $sync = false,
            $return_last_error = false,
            $return_iblock_id = true
        );
    }
}
