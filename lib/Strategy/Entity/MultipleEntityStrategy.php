<?php

namespace Inetris\Nocode\Strategy\Entity;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\SystemException;

class MultipleEntityStrategy implements EntityInterface
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
        return sprintf('PROPS_MULTI_%s', $iblockId);
    }

    public static function getTableName(int $iblockId): string
    {
        return sprintf('b_iblock_element_prop_m%s', $iblockId);
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
            new Fields\IntegerField('IBLOCK_ELEMENT_ID', ['required' => true]),
            new Fields\IntegerField('IBLOCK_PROPERTY_ID', ['required' => true]),
            new Fields\TextField('VALUE', ['required' => true]),
            new Fields\IntegerField('VALUE_ENUM', []),
            new Fields\FloatField('VALUE_NUM', []),
            new Fields\StringField('DESCRIPTION', []),
        ];
    }
}

