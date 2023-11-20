<?php

namespace Inetris\Nocode\Strategy\Condition;

use Inetris\Nocode\DTO;
use Inetris\Nocode\Tools\Tools;

class OutStrategy implements ConditionInterface
{
    public function handle(DTO\ConditionDTO $condition, $arResult): array
    {
        $operation = $condition->operation;
        $criterion = $condition->criterion;
        $value = $condition->value;
        $rule = $condition->rule;
        $suborder = ($condition->suborder) ?: 0;
        $field = $condition->field;

        $arFilterAll = array();

        if (!empty($operation["PROP"]["PREF"]["VALUE"])) {
            $value1 = $operation["PROP"]["PREF"]["VALUE"];
        } elseif (!empty($value)) {
            if ($criterion["PROPERTY_TYPE_CODE"] == "time") {
                    $value1 = date("d.m.Y H:i:s", strtotime($value . " midnight"));
            } else {
                $value1 = preg_replace_callback('/\{(.*?)}/', function ($m) use ($arResult) {
                    return $arResult[$m[1]] ?? $m[0];
                }, $value);

                if ($operation["PROP"]["PROCESS"]["VALUE_XML_ID"] == "search") {
                    $value1 = "%" . $value1 . "%";
                }
            }
        } else {
            $value1 = $arResult[$criterion["PROP"]["CODE_IN"]["VALUE"]];
        }

        if ($field == array()) {
            $dbCodeReal = $criterion["PROP"]["CODE_OUT"]["VALUE"];
        } elseif (in_array($field["PROPERTY_TYPE"] . $field["USER_TYPE"], array("E", "L", "F", "SUserID"))) {
            $dbCodeReal = $criterion["PROP"]["CODE_OUT"]["VALUE"];
        } else {
            if ($field["MULTIPLE"] == "N") {
                $dbCodeReal = "PROPERTY_S.PROPERTY_" . $field["ID"];
            } else {
                $dbCodeReal = "PROPERTY_{$field['CODE']}.VALUE";
            }
        }

        $tempFilter["data"][$operation["PROP"]["OPER"]["~VALUE"] . $dbCodeReal] = $value1;
        $tempFilter["id"] = $condition->id;
        $tempFilter["parent_id"] = $suborder;
        $arFilterAll["filter"][$condition->id] = $tempFilter;

        if ($field["CODE"]) {
            $arFilterAll["fields"][$dbCodeReal] = $field["CODE"];
        }

        return $arFilterAll;
    }
}
