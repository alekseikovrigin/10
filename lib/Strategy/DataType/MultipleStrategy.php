<?php

namespace Inetris\Nocode\Strategy\DataType;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;
use Inetris\Nocode\Strategy\Entity;
use Inetris\Nocode\Constants\TypeToClassMapper;

class MultipleStrategy implements DataTypeInterface
{
    /**
     * @throws ArgumentException
     * @throws SystemException
     */
    public function handle($query, $iblockID = null, $field = null): void
    {
        $middleField = "PROPERTY_{$field['code']}";
        $query->registerRuntimeField(
            (new Reference(
                $middleField,
                Entity\MultipleEntityStrategy::getEntityName($iblockID),
                Join::on('this.ID', 'ref.IBLOCK_ELEMENT_ID')
                    ->whereIn("ref.IBLOCK_PROPERTY_ID", [$field["id"]])
            ))->configureJoinType("LEFT")
        );
        if (in_array($field["type"], array("E", "L", "F", "SUserID"))) {
            $query->registerRuntimeField(
                (new Reference(
                    $field["code"],
                    TypeToClassMapper::getReferenceClass($field["type"]),
                    Join::on("this.$middleField.VALUE", 'ref.ID')
                        ->whereIn("this.$middleField.IBLOCK_PROPERTY_ID", [$field["id"]])
                ))->configureJoinType("LEFT")
            );
        }
    }
}
