<?php

namespace Inetris\Nocode\Repository;

use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\Connection;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Text\Converter;
use CIBlockElement;

class IBlockRepository implements IBlockRepositoryInterface
{
    /**
     * @param array $arFilter
     * @param array $arSelect
     * @param int $pageSize
     * @return array
     */
    public function getElementsByFilter(array $arFilter, array $arSelect, int $pageSize = 50): array
    {
        $result = [];
        $res = CIBlockElement::GetList([], $arFilter, false, ["nPageSize" => $pageSize], $arSelect);
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $arProps = $ob->GetProperties();

            $result[$arFields["ID"]] = $arFields;
            $result[$arFields["ID"]]["PROP"] = $arProps;
        }
        return $result;
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public function getIblockProperties($iblockId): array
    {
        $result = [];
        $properties = PropertyTable::getList([
            'select' => ['*'],
            'filter' => ['IBLOCK_ID' => $iblockId],
        ]);

        while ($property = $properties->fetch()) {
            $result[] = $property;
        }

        return $result;
    }

    /**
     * @param $iblockType
     * @return array
     */
    public function getIBlocksByType($iblockType): array
    {
        $result = [];
        $dbRes = \CIBlock::GetList([], ['TYPE' => $iblockType, 'ACTIVE' => 'Y']);

        while ($iblock = $dbRes->Fetch()) {
            $result[$iblock['ID']] = $iblock;
        }

        return $result;
    }

    /**
     * @param $iblockId
     * @return array
     */
    public function getConditionTypesByIblock($iblockId): array
    {
        $result = [];
        $types = \CIBlockPropertyEnum::GetList([], ["IBLOCK_ID" => $iblockId, "CODE" => "TYPE"]);

        while ($type = $types->Fetch()) {
            $result[$type["ID"]] = $type;
        }

        return $result;
    }

    /**
     * @param Connection $connection
     * @param string $query
     * @return array
     * @throws SqlQueryException
     */
    public function executeSqlQuery(Connection $connection, string $query): array
    {
        $result = $connection->query($query);
        $arrayResult = [];

        while ($record = $result->fetch(Converter::getHtmlConverter())) {
            $arrayResult[] = $record;
        }
        return $arrayResult;
    }


    /**
     * @param Query $query
     * @return array
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function executeQuery(Query $query): array
    {
        return $query->exec()->fetchAll();
    }


}
