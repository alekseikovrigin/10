<?php

namespace Inetris\Nocode\Strategy\Filter;

class StringFilterStrategy implements FilterInterface
{
    public function createFilter($value, $dbField, $operationType): array
    {
        $processingType = $operationType["PROP"]["PROCESS"]["VALUE_XML_ID"];
        $reverseOperation = $operationType["PROP"]["OPERNOT"]["~VALUE"];
        $reverseValue = $operationType["PROP"]["PREFNOT"]["VALUE"];

        if ($processingType == "strong") {
            $value = "'" . $value . "'";
            $operation = str_replace(array("!%", "!", "?%", "?"),
                array("not like ", "!=", "like ", "like "), $reverseOperation);

        } elseif ($processingType == "search") {
            $operation = 'instr(' . "'" . $value . "'" . ', ' . $dbField . ')';
            $value = $reverseOperation . $reverseValue;
            $dbField = "";
        } else {
            $value = "'%" . $value . "%'";
            $operation = str_replace(array("!%", "!", "?%", "?"),
                array("not like ", "!=", "like ", "like "), $reverseOperation);
        }


        return [$dbField, $operation, $value];
    }
}
