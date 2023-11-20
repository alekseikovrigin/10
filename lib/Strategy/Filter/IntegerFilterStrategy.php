<?php

namespace Inetris\Nocode\Strategy\Filter;

class IntegerFilterStrategy implements FilterInterface
{
    public function createFilter($value, $dbField, $operationType): array
    {
        $reverseOperation = $operationType["PROP"]["OPERNOT"]["~VALUE"];
        $operation = str_replace(array("!", ""),
            array("not like", ""), $reverseOperation);

        return [$dbField, $operation, $value];
    }
}
