<?php

namespace Inetris\Nocode\Strategy\DataType;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\SystemException;

class IBlockSectionStrategy implements DataTypeInterface
{
    /**
     * @throws SystemException
     * @throws ArgumentException
     */
    public function handle($query, $iblockID = null, $field = null)
    {
        $entityName = "section";
        if (class_exists($entityName . "Table")) {
            $entityDataClass = "\\" . $entityName . "Table";
        } else {
            $entityDataClass = Base::compileEntity(
                $entityName,
                array(
                    'ID' => array('data_type' => 'integer'),
                    'SORT' => array('data_type' => 'integer'),
                    'IBLOCK_ID' => array('data_type' => 'integer'),
                    'ACTIVE_FROM' => array('data_type' => 'string'),
                    'CODE' => array('data_type' => 'string'),
                ),
                array('table_name' => 'b_iblock_section')
            );
        }
        $query->registerRuntimeField('b_iblock_section', array(
                'data_type' => $entityDataClass,
                'reference' => array(
                    '=this.SECTION_ID' => 'ref.ID',
                ),
            )
        );
    }
}
