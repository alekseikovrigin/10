<?php

namespace Inetris\Nocode\Strategy\Condition;

use Inetris\Nocode\DTO;
use Inetris\Nocode\DTO\ConditionDTO;

class SortStrategy implements ConditionInterface
{
    /**
     * @param ConditionDTO $condition
     * @param $arResult
     * @return array
     */
    public function handle(DTO\ConditionDTO $condition, $arResult): array
    {
        $criterion = $condition->criterion;
        $value = $condition->value;
        $rule = $condition->rule;

        $arFilterAll[$rule]["order"][$criterion["PROP"]["CODE_OUT"]["VALUE"]] = $value;
        return $arFilterAll[$rule];
    }
}
