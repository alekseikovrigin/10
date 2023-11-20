<?php

namespace Inetris\Nocode\Strategy\DataType;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Inetris\Nocode\Constants\TypeToClassMapper;
use Inetris\Nocode\Strategy\Entity;

class SingleStrategy implements DataTypeInterface
{
    /**
     * @throws ArgumentException
     * @throws SystemException
     */
    public function handle($query, $iblockID = null, $field = null)
    {
        $middleField = "PROPERTY_S";
        $query->registerRuntimeField(
            (new Reference(
                $middleField,
                Entity\SingleEntityStrategy::getEntityName($iblockID),
                Join::on('this.ID', 'ref.IBLOCK_ELEMENT_ID')
            ))->configureJoinType("LEFT")
        );
        if (in_array($field["type"], array("E", "L", "F"))) {
            $query->registerRuntimeField(
                (new Reference(
                    $field["code"],
                    TypeToClassMapper::getReferenceClass($field["type"]),
                    Join::on("this.$middleField.PROPERTY_{$field["id"]}", 'ref.ID')
                ))->configureJoinType("LEFT")
            );
        }
    }
}
