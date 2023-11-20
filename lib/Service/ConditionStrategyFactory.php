<?php

namespace Inetris\Nocode\Service;

use Inetris\Nocode\Strategy\Condition\ConditionInterface;
use InvalidArgumentException;
use Inetris\Nocode\Strategy\Condition;

class ConditionStrategyFactory
{
    /**
     * @param string $type
     * @return ConditionInterface|null
     */
    public static function create(string $type): ?Condition\ConditionInterface
    {
        switch ($type) {
            case "logic":
                return new Condition\LogicStrategy();
            case "out":
                return new Condition\OutStrategy();
            case "sort":
                return new Condition\SortStrategy();
            case "limit":
                return new Condition\LimitStrategy();
            case "priority":
                return new Condition\PriorityStrategy();
            case "in":
                return null;
            default:
                throw new InvalidArgumentException("Unknown strategy type: $type");
        }
    }
}
