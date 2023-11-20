<?php

namespace Inetris\Nocode\Service;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\SystemException;
use Inetris\Nocode\Repository\CachedIBlockRepository;
use Inetris\Nocode\Tools\Tools;

class IBlockDataService
{
    private $cachedRepository;
    public $iblocks;

    public $cacheTime = 60 * 60;

    public $cacheTimePermanent = 60 * 60 * 24;

    public function __construct(
        CachedIBlockRepository $cachedRepository
    )
    {
        $this->cachedRepository = $cachedRepository;
    }

    /**
     * @param array $properties
     * @return array
     */
    public function assignParams(array $properties): array
    {
        $dbField = [];

        $rule = Tools::getParam('LINK', $properties);
        $criterion = Tools::getParam('CRIT', $properties);
        $operation = Tools::getParam('OPER', $properties);
        $value = Tools::getParam('VALU', $properties);
        $suborder = Tools::getParam('SUBORDER', $properties);
        $type = Tools::getParam('TYPE', $properties);

        $dbField['PROPERTY_LINK'] = "`PROPERTY_$rule`";
        $dbField['PROPERTY_CRITERION'] = "`PROPERTY_$criterion`";
        $dbField['PROPERTY_OPERATION'] = "`PROPERTY_$operation`";
        $dbField['PROPERTY_VALUE'] = "`PROPERTY_$value`";
        $dbField['PROPERTY_SUBORDER'] = "`PROPERTY_$suborder`";
        $dbField['PROPERTY_TYPE'] = "`PROPERTY_$type`";

        return $dbField;
    }

    /**
     * @return array
     */
    public function getCriterions(): array
    {
        $arFilter["IBLOCK_ID"] = Tools::getParam('nocode_criterions', $this->iblocks);
        $arFilter["ACTIVE"] = "Y";
        $arSelect = array("ID", "IBLOCK_ID", "NAME", "CODE", "PROPERTY_*", "PROPERTY_TYPE.CODE");
        $this->cachedRepository->setCacheTime($this->cacheTimePermanent);
        return $this->cachedRepository->getElementsByFilter($arFilter, $arSelect, 51);
    }

    /**
     * @return array
     */
    public function getOperations(): array
    {
        $arFilter["IBLOCK_ID"] = Tools::getParam('nocode_operations', $this->iblocks);
        $arSelect = array("ID", "IBLOCK_ID", "NAME", "CODE", "PROPERTY_*");
        $this->cachedRepository->setCacheTime($this->cacheTimePermanent);
        return $this->cachedRepository->getElementsByFilter($arFilter, $arSelect, 51);
    }

    /**
     * @return array
     */
    public function getOperationTypes(): array
    {
        $arFilter["IBLOCK_ID"] = Tools::getParam('nocode_types', $this->iblocks);
        $arSelect = array("ID", "IBLOCK_ID", "NAME", "CODE", "PROPERTY_*");
        $this->cachedRepository->setCacheTime($this->cacheTimePermanent);
        return $this->cachedRepository->getElementsByFilter($arFilter, $arSelect, 51);
    }


    /**
     * @param string $rulesCategory
     * @return array
     */
    public function getAllRules(string $rulesCategory): array
    {
        $arFilter["IBLOCK_ID"] = Tools::getParam('nocode_rules', $this->iblocks);
        $arFilter["SECTION_CODE"] = $rulesCategory;
        $arFilter["ACTIVE"] = "Y";
        $arSelect = array("ID", "SORT", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM");
        $this->cachedRepository->setCacheTime($this->cacheTimePermanent);
        return $this->cachedRepository->getElementsByFilter($arFilter, $arSelect, 51);
    }

    /**
     * @param $iblockId
     * @return array
     */
    public function getProperties($iblockId): array
    {
        $arFilter["IBLOCK_ID"] = $iblockId;
        $this->cachedRepository->setCacheTime($this->cacheTimePermanent);
        return $this->cachedRepository->getIblockProperties($arFilter["IBLOCK_ID"]);
    }

    /**
     * @param $iblockType
     * @return array
     */
    public function getIblocks($iblockType): array
    {
        $arFilter["IBLOCK_TYPE"] = $iblockType;
        $this->cachedRepository->setCacheTime($this->cacheTimePermanent);
        return $this->cachedRepository->getIBlocksByType($arFilter["IBLOCK_TYPE"]);
    }

    /**
     * @return array
     */
    public function getTypeEnumIds(): array
    {
        $arFilter["IBLOCK_ID"] = Tools::getParam('nocode_conditions', $this->iblocks);
        $this->cachedRepository->setCacheTime($this->cacheTimePermanent);
        return $this->cachedRepository->getConditionTypesByIblock($arFilter["IBLOCK_ID"]);
    }

    /**
     * @param string $filter
     * @return array
     */
    public function getConditions(string $filter): array
    {
        $connection = Application::getConnection();
        $this->cachedRepository->setCacheTime($this->cacheTimePermanent);
        return $this->cachedRepository->executeSqlQuery($connection, $filter);
    }

    /**
     * @param Query $query
     * @return array
     * @throws ArgumentException
     * @throws SystemException
     */
    public function getElements(Query $query): array
    {
        $this->cachedRepository->setCacheTime($this->cacheTime);
        return $this->cachedRepository->executeQuery($query);
    }


}
