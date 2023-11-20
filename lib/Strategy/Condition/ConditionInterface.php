<?php

namespace Inetris\Nocode\Strategy\Condition;

use Inetris\Nocode\DTO;

interface ConditionInterface
{
    public function handle(DTO\ConditionDTO $condition, $arResult): ?array;
}
