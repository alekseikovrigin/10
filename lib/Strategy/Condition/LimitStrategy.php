<?php

namespace Inetris\Nocode\Strategy\Condition;

use Inetris\Nocode\DTO\ConditionDTO;

class LimitStrategy implements ConditionInterface
{
    /**
     * @param ConditionDTO $condition
     * @param $arResult
     * @return array
     */
    public function handle(ConditionDTO $condition, $arResult): array
    {
        $value = $condition->value;
        $rule = $condition->rule;

        $arFilterAll[$rule]["limit"] = (!empty($value)) ? $value : 10;
        return $arFilterAll[$rule];
    }
}
