<?php

namespace Inetris\Nocode\Strategy\Condition;

use Inetris\Nocode\DTO;
use Inetris\Nocode\DTO\ConditionDTO;

class PriorityStrategy implements ConditionInterface
{
    /**
     * @param ConditionDTO $condition
     * @param $arResult
     * @return array
     */
    public function handle(DTO\ConditionDTO $condition, $arResult): array
    {
        $value = $condition->value;
        $rule = $condition->rule;

        $arFilterAll[$rule]["priority"] = $value;
        return $arFilterAll[$rule];
    }
}
