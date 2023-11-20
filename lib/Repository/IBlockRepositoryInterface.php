<?php

namespace Inetris\Nocode\Repository;

interface IBlockRepositoryInterface
{
    public function getElementsByFilter(array $arFilter, array $arSelect, int $pageSize);

    public function getIblockProperties($iblockId);

    public function getIBlocksByType($iblockType);

    public function getConditionTypesByIblock($iblockId);

    public function executeQuery(\Bitrix\Main\Entity\Query $query);

    public function executeSqlQuery(\Bitrix\Main\DB\Connection $connection, string $query);

}
