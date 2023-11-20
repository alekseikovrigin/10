<?php

namespace Inetris\Nocode\Strategy\Entity;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\SystemException;

class ElementEntityStrategy implements EntityInterface
{
    protected $fields;
    protected $iblockId;

    public function __construct($iblockId, $fields = null)
    {
        $this->iblockId = $iblockId;
        $this->fields = $fields;
    }

    public static function getEntityName(int $iblockId): string
    {
        return "element";
    }

    public static function getTableName(int $iblockId): string
    {
        return 'b_iblock_element';
    }

    /**
     * @throws SystemException
     * @throws ArgumentException
     */
    public function compileEntity(): Base
    {
        $entityName = self::getEntityName($this->iblockId) . "Table";

        if (class_exists($entityName)) {
            return $entityName::getEntity();
        }

        return Base::compileEntity(
            self::getEntityName($this->iblockId),
            $this->multiplePropertiesTable($this->fields),
            ['table_name' => self::getTableName($this->iblockId)]
        );
    }

    /**
     * @throws SystemException
     */
    public function multiplePropertiesTable($fields): array
    {
        return [
            new Fields\IntegerField('ID', ['primary' => true, 'autocomplete' => true]),
            new Fields\IntegerField('IBLOCK_ID', []),
            new Fields\StringField('NAME', []),
            new Fields\StringField('CODE', []),
            new Fields\TextField('PREVIEW_TEXT', []),
            new Fields\IntegerField('DETAIL_PICTURE', []),
            new Fields\IntegerField('IBLOCK_SECTION_ID', []),
            new Fields\IntegerField('SHOW_COUNTER', []),
            new Fields\IntegerField('SORT', []),
            new Fields\IntegerField('ACTIVE_FROM', []),
            new Fields\DatetimeField('DATE_CREATE', []),
        ];
    }
}

