<?php

namespace Inetris\Nocode\Strategy\Condition;

use Inetris\Nocode\DTO;

class LogicStrategy implements ConditionInterface
{
    public function handle(DTO\ConditionDTO $condition, $arResult): ?array
    {
        $id = $condition->id;
        $value = $condition->value;
        $suborder = $condition->suborder;

        $filter["filter"][$id]["data"]["LOGIC"] = $value;
        $filter["filter"][$id]["id"] = $id;
        $filter["filter"][$id]["parent_id"] = $suborder ?: 0;

        return $filter;
    }
}
