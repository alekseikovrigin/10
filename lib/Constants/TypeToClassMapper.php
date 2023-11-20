<?php

namespace Inetris\Nocode\Constants;

use Bitrix\Iblock;
use Bitrix\Main;
use Bitrix\Main\Entity;

class TypeToClassMapper
{
    /**
     * @param string $propertyType
     * @return string
     */
    static function getReferenceClass(string $propertyType): string
    {
        $tableClass = "";
        if ($propertyType == 'E') {
            $tableClass = Iblock\ElementTable::class;
        } elseif ($propertyType == 'F') {
            $tableClass = Main\FileTable::class;
        } elseif ($propertyType == 'L') {
            $tableClass = Iblock\PropertyEnumerationTable::class;
        } elseif ($propertyType == 'SUserID') {
            $tableClass = Main\UserTable::class;
        }
        return $tableClass;
    }

    /**
     * @param string $propertyType
     * @return string
     */
    static function getFieldClass(string $propertyType): string
    {
        if ($propertyType == 'S') {
            $tableClass = Entity\StringField::class;
        } elseif ($propertyType == 'N') {
            $tableClass = Entity\IntegerField::class;
        } elseif ($propertyType == 'E') {
            $tableClass = Entity\IntegerField::class;
        } else {
            $tableClass = Entity\IntegerField::class;
        }
        return $tableClass;
    }
}
