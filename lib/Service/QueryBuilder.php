<?php

namespace Inetris\Nocode\Service;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\SystemException;
use Inetris\Nocode\Tools\Tools;
use Inetris\Nocode\Strategy;
use Inetris\Nocode\DTO;
use Bitrix\Main\Context;

/**
 *
 */
class QueryBuilder
{
    /**
     * @var
     */
    public $properties;
    /**
     * @var
     */
    public $conditionTypes;
    /**
     * @var
     */
    public $types;
    /**
     * @var
     */
    public $operationsByType;
    /**
     * @var
     */
    public $dbField;

    /**
     * @var
     */
    public $operations;
    /**
     * @var
     */
    public $criterions;
    /**
     * @var
     */
    public $arCritExist;

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @return array
     */
    public function mapTypes(): array
    {
        $groups = [];
        foreach ($this->operations as $item) {
            foreach ($item['PROP']['OP_TYPE']['~VALUE'] as $value) {
                $groups[$this->types[$value]["ID"]][] = $item["ID"];
            }
        }
        return $groups;
    }

    /**
     * @return void
     */
    public function addHandlers(): void
    {
        foreach ($this->arCritExist as $crit) {
            if (!empty($crit["PROP"]["HANDLER"]["VALUE"])) {
                $this->arCritExist[$crit["ID"]]["PROP"]["CODE_IN"]["VALUE"] = $crit["PROP"]["HANDLER"]["VALUE"] . "(" . $crit["PROP"]["CODE_IN"]["VALUE"] . ")";
            }
        }
    }

    /**
     * @param array $arCondition
     * @return DTO\ConditionDTO
     */
    public function createConditionDTO(array $arCondition): DTO\ConditionDTO
    {
        $operation = $this->operations[$arCondition["PROPERTY_OPER"]];
        $criterion = $this->criterions[$arCondition["PROPERTY_CRIT"]];
        $type = $this->conditionTypes[$arCondition["PROPERTY_TYPE"]]["XML_ID"];
        $fieldCode = explode(".", $criterion["PROP"]["CODE_OUT"]["VALUE"])["0"];
        $field = $this->properties[$fieldCode];

        $id = $arCondition["IBLOCK_ELEMENT_ID"];
        $value = $arCondition["PROPERTY_VALU"];
        $rule = $arCondition["PROPERTY_LINK"];
        $suborder = $arCondition["PROPERTY_SUBORDER"];

        return new DTO\ConditionDTO($rule, $type, $criterion, $operation, $value, $suborder, $id, $field);
    }

    /**
     * @param $idCriterion
     * @param $idValue
     * @param string $fieldCondition
     * @param $field
     * @param $operation
     * @param $value
     * @return string
     */
    function buildSqlCondition($idCriterion, $idValue, string $fieldCondition = "=", $field = null, $operation = null, $value = null): string
    {
        $dbField = $this->dbField;
        $in = Tools::getParam("in", $this->conditionTypes, "XML_ID");

        $condition = "
        OR  (   {$dbField['PROPERTY_TYPE']} = {$in}
                AND {$dbField['PROPERTY_CRITERION']} = {$idCriterion}
                AND {$dbField['PROPERTY_OPERATION']} $fieldCondition {$idValue}
        ";

        if ($operation !== null && $value !== null) {
            $condition .= "
                AND $field $operation $value";
        }

        $condition .= ")";

        return $condition;
    }

    /**
     * @param $arCritsExist
     * @return array
     */
    public function prepareInputParams($arCritsExist): array
    {
        $globalCriterions = array();

        foreach ($arCritsExist as $key => $value) {
            $criterion = $value["PROP"]["CODE_IN"]["VALUE"];
            if (
                strpos($criterion, '_SERVER') !== false
                || strpos($criterion, '_COOKIE') !== false
                || strpos($criterion, '_SESSION') !== false
            ) {
                $temp = str_replace(array('$', ']', '"', "'", chr(38), chr(34)), '', $criterion); //WARN double quotes
                $var = explode('[', $temp);

                $handler = $this->criterions[$key]["PROP"]["HANDLER"]["VALUE"];

                if ($var["0"] == "_SERVER") {
                    $context = Context::getCurrent()->getServer();


                    if (!empty($handler)) {
                        if ($handler == "getHost") {
                            $value = parse_url($context->get($var['1']), PHP_URL_HOST);
                        } elseif ($handler == "getFirstCatalog") {
                            $tempUrl = explode("/", parse_url($context->get($var['1']), PHP_URL_PATH));
                            $value = $tempUrl["1"]; //cause first catalog is null
                        }
                        $globalCriterions[$handler . "(" . $criterion . ")"] = $value;
                    } else {
                        $globalCriterions[$criterion] = $context->get($var['1']);
                    }
                }
            }
        }

        return $globalCriterions;
    }

    /**
     * @param $arResult
     * @param $iblockId
     * @param $selectedRulesIds
     * @return string
     */
    public function formInFilter($arResult, $iblockId, $selectedRulesIds): string
    {
        $arOper = $this->operations;
        $arCritExist = $this->arCritExist;

        $dbField = $this->dbField;

        $strFilter = "
        SELECT
            `element_prop_s$iblockId`.`IBLOCK_ELEMENT_ID` AS `IBLOCK_ELEMENT_ID`,
            `element_prop_s$iblockId`.{$dbField['PROPERTY_LINK']} AS `PROPERTY_LINK`,
            `element_prop_s$iblockId`.{$dbField['PROPERTY_CRITERION']} AS `PROPERTY_CRIT`,
            `element_prop_s$iblockId`.{$dbField['PROPERTY_OPERATION']} AS `PROPERTY_OPER`,
            `element_prop_s$iblockId`.{$dbField['PROPERTY_VALUE']} AS `PROPERTY_VALU`,
            `element_prop_s$iblockId`.{$dbField['PROPERTY_SUBORDER']} AS `PROPERTY_SUBORDER`,
            `element_prop_s$iblockId`.{$dbField['PROPERTY_TYPE']} AS `PROPERTY_TYPE`
        FROM `b_iblock_element_prop_s$iblockId` `element_prop_s$iblockId`
        WHERE
            ({$dbField['PROPERTY_LINK']} IN ($selectedRulesIds))
        AND	({$dbField['PROPERTY_LINK']} NOT IN (SELECT
            `element_prop_s$iblockId`.{$dbField['PROPERTY_LINK']} AS `PROPERTY_LINK`
        FROM `b_iblock_element_prop_s$iblockId` `element_prop_s$iblockId`";
        $strFilter .= "WHERE (
        ({$dbField['PROPERTY_LINK']} IS NOT NULL)
        AND ( 1>1 "; //TODO exclude deactivated

        $filterContext = new Strategy\Filter\FilterContext();

        foreach ($arCritExist as $idCriterion => $criterion) {
            $codeCriterion = $criterion["PROP"]["CODE_IN"]["VALUE"];

            if (array_key_exists($codeCriterion, $arResult) && (!empty($arResult[$codeCriterion]))) {
                $type = $criterion["PROP"]["TYPE"]["VALUE"];
                if ($type == Tools::getParam('string', $this->types)) {
                    $filterContext->setStrategy(new Strategy\Filter\StringFilterStrategy());
                } elseif ($type == Tools::getParam('integer', $this->types)) {
                    $filterContext->setStrategy(new Strategy\Filter\IntegerFilterStrategy());
                }

                foreach ($this->operationsByType[$type] as $val) {
                    if (empty($arOper[$val]["PROP"]["PREFNOT"]["VALUE"])) {
                        list($resField, $resOperation, $resValue) = $filterContext->createFilter($arResult[$codeCriterion], $dbField['PROPERTY_VALUE'], $arOper[$val]);
                        $strFilter .= $this->buildSqlCondition($idCriterion, $val, "=", $resField, $resOperation, $resValue);
                    }
                }
                $strFilter .= $this->buildSqlCondition($idCriterion, Tools::getParam('null', $this->operations));
            } else {
                $strFilter .= $this->buildSqlCondition($idCriterion, Tools::getParam('null', $this->operations), "!=");
            }
        }

        $strFilter .= "))
        GROUP BY `element_prop_s$iblockId`.{$dbField['PROPERTY_LINK']}))
        ";

        return $strFilter;
    }

    /**
     * @param array $arResult
     * @param array $arConditions
     * @return array
     */
    public function formOutFilter(array $arResult, array $arConditions): array
    {
        $arFilter = array();
        if (!empty($arConditions)) {
            foreach ($arConditions as $arCondition) {
                $conditionDTO = $this->createConditionDTO($arCondition);
                $strategy = ConditionStrategyFactory::create($conditionDTO->type);
                if ($strategy instanceof Strategy\Condition\ConditionInterface) {
                    $strategyResult = $strategy->handle($conditionDTO, $arResult);

                    $arFilter[$conditionDTO->rule] = Tools::mergeArrays([
                        $arFilter[$conditionDTO->rule] ?? [],
                        $strategyResult
                    ]);
                }
            }
        }
        return $arFilter;
    }

    /**
     * @param array $filter
     * @param int $rulesCount
     * @return array
     */
    public function processingOutFilter(array $filter, int $rulesCount): array
    {
        uasort($filter, function ($a, $b) {
            if ($a['priority'] ?? 0 == $b['priority'] ?? 0) {
                return 0;
            }
            return ($a['priority'] ?? 0 > $b['priority'] ?? 0) ? -1 : 1;
        });
        return array_slice($filter, 0, $rulesCount);
    }

    /**
     * @param $fieldsFromRules
     * @return array
     * @throws SystemException
     */
    private function sortFields($fieldsFromRules): array
    {
        $fields = array();
        foreach ($fieldsFromRules as $fieldCode) {
            $tmp = array();
            $field = $this->properties[$fieldCode];

            if ($field["MULTIPLE"] == "N") {
                if (in_array($field["PROPERTY_TYPE"], array("S"))) {
                    $singleFields["single"][] = new Fields\StringField("PROPERTY_" . $field["ID"], []);
                } else {
                    $singleFields["single"][] = new Fields\IntegerField("PROPERTY_" . $field["ID"], []);
                }
                $typeField = "single";
            } else {
                $typeField = "multiple";
            }

            $tmp["id"] = $field["ID"];
            $tmp["code"] = $field["CODE"];
            $tmp["type"] = $field["PROPERTY_TYPE"] . $field["USER_TYPE"];
            $tmp["multiple"] = $field["MULTIPLE"];
            $fields["byTypes"][$typeField][$field["CODE"]] = $tmp;
            $fields["forEntities"] = $singleFields;
        }
        return $fields;
    }

    /**
     * @param array $elements
     * @param int $parentId
     * @return array
     * @throws ArgumentNullException
     * @throws ArgumentTypeException
     */
    public function buildHierarchy(array $elements, int $parentId = 0): array
    {
        $branch = array();
        $ids = array_column($elements, 'id');

        foreach ($elements as $element) {

            if ($element['parent_id'] != 0 && !in_array($element['parent_id'], $ids)) {
                throw new \Exception("Родитель с ID {$element['parent_id']} не существует");
            }

            if ($element['parent_id'] == $parentId) {
                $id = $element['id'];

                $children = $this->buildHierarchy($elements, $id);
                if ($children) {
                    foreach ($children as $k => $child){
                        $element["data"][$k] = $child;
                    }
                }

                // Проверка, что в ветке есть что-то кроме LOGIC
                if (count($element["data"]) == 1 && array_key_exists('LOGIC', $element["data"])) {
                    throw new \Exception("В ветке с ID $id нет данных кроме LOGIC");
                }

                $branch[$id] = $element["data"];
            }
        }

        return $branch;
    }

    /**
     * @throws ArgumentNullException
     * @throws ArgumentTypeException
     * @throws ArgumentException
     * @throws SystemException
     */
    public function formQuery(int $iblockId, array $rule, $Item_Count, $blackList): Query
    {
        $idField = "ID";

        $tempSelect = $rule["select"];
        $tempSelect[] = $idField;

        // Пример использования
        $elements = [


                1 => ['id' => 1, 'parent_id' => 0, 'data' => ['LOGIC' => 'OR']],
                2 => ['id' => 2, 'parent_id' => 1, 'data' => ['LOGIC' => 'AND']],
                3 => ['id' => 3, 'parent_id' => 1, 'data' => ['>=DATE_CREATE' => '15.11.2023 00:00:00']],
                4 => ['id' => 4, 'parent_id' => 2, 'data' => ['>SORT' => '500']],
                5 => ['id' => 5, 'parent_id' => 2, 'data' => ['<=SORT' => '700']]

        ];

        $hierarchy = $this->buildHierarchy($elements);
        i($hierarchy);
        i($elements);
        i($rule["filter"]);

        $tempFilter = $this->buildHierarchy($rule["filter"]);
        $tempFilter["!" . $idField] = $blackList;
        $tempFilter["IBLOCK_ID"] = $iblockId;

        if ($Item_Count > $rule["limit"] && $rule["limit"] > 0) {
            $tempLimit = $rule["limit"];
        } else {
            $tempLimit = $Item_Count;
        }
        $tempLimit = ($tempLimit > 0) ? $tempLimit : 10; //TODO

        $tempOrder = (!empty($rule["order"])) ? $rule["order"] : array($idField => "DESC");

        $res = $this->sortFields($rule["fields"]);
        $fields = $res["byTypes"];
        $forEntities = $res["forEntities"];

        $strategy = EntityStrategyFactory::getStrategy("element", $iblockId);
        $class = $strategy->compileEntity();
i($tempFilter);
        $query = new Query($class);
        $query
            ->setSelect($tempSelect)
            ->setFilter($tempFilter)
            ->setOrder($tempOrder)
            ->setLimit($tempLimit);
        $query->setGroup($idField);

        foreach ($fields as $type => $fieldGroup) {
            $entityStrategy = EntityStrategyFactory::getStrategy($type, $iblockId, $forEntities[$type]);
            $entityStrategy->compileEntity();

            foreach ($fieldGroup as $field) {
                $fieldStrategy = FieldStrategyFactory::getStrategy($type, $iblockId);
                $fieldStrategy->handle($query, $iblockId, $field);
            }
        }

        return $query;
    }

}
