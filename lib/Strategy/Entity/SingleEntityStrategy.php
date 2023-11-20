<?php

namespace Inetris\Nocode\Strategy\Entity;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\SystemException;

class SingleEntityStrategy implements EntityInterface
{
    protected $fields;
    protected $iblockId;

    public function __construct(int $iblockId, $fields = null)
    {
        $this->iblockId = $iblockId;
        $this->fields = $fields;
    }

    public static function getEntityName(int $iblockId): string
    {
        return sprintf('PROPS_SINGLE_%s', $iblockId);
    }

    public static function getTableName(int $iblockId): string
    {
        return sprintf('b_iblock_element_prop_s%s', $iblockId);
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
        $primaryField = new Entity\IntegerField('IBLOCK_ELEMENT_ID', array(
            'primary' => true
        ));
        array_unshift($fields, $primaryField);

        return $fields;
    }
}
