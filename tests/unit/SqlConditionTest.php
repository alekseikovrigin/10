<?php

namespace Inetris\Nocode;

class SqlConditionTest extends BitrixTestCase
{
    public function sqlConditionProvider(): array
    {
        return [
            // idCriterion, idValue, fieldCondition, field, operation, value, expected
            [1, 2, '!=', null, null, null, "OR(`PROPERTY_2489`=761AND`PROPERTY_2486`=1AND`PROPERTY_2487`!=2)"],
            [1, 2, null, null, null, null, "OR(`PROPERTY_2489`=761AND`PROPERTY_2486`=1AND`PROPERTY_2487`=2)"],
            [1, 2, '=', "`PROPERTY_2484`", "not like", 1, "OR(`PROPERTY_2489`=761AND`PROPERTY_2486`=1AND`PROPERTY_2487`=2AND`PROPERTY_2484`notlike1)"],
        ];
    }

    /**
     * @dataProvider sqlConditionProvider
     * @test
     */
    public function buildSqlCondition($idCriterion, $idValue, $fieldCondition, $field, $operation, $value, $expected)
    {
        $object = new Service\QueryBuilder();
        $object->dbField =
            array (
                'PROPERTY_LINK' => '`PROPERTY_2488`',
                'PROPERTY_CRITERION' => '`PROPERTY_2486`',
                'PROPERTY_OPERATION' => '`PROPERTY_2487`',
                'PROPERTY_VALUE' => '`PROPERTY_2484`',
                'PROPERTY_SUBORDER' => '`PROPERTY_2485`',
                'PROPERTY_TYPE' => '`PROPERTY_2489`',
            );
        $object->conditionTypes = array(
            761 =>
                array(
                    'ID' => 761,
                    'PROPERTY_ID' => 2489,
                    'VALUE' => 'если',
                    'DEF' => 'N',
                    'SORT' => 500,
                    'XML_ID' => 'in',
                    'TMP_ID' => NULL,
                    'EXTERNAL_ID' => 'in',
                    'PROPERTY_NAME' => 'Тип',
                    'PROPERTY_CODE' => 'TYPE',
                    'PROPERTY_SORT' => 100,
                ),
            763 =>
                array(
                    'ID' => 763,
                    'PROPERTY_ID' => 2489,
                    'VALUE' => 'количество',
                    'DEF' => 'N',
                    'SORT' => 500,
                    'XML_ID' => 'limit',
                    'TMP_ID' => NULL,
                    'EXTERNAL_ID' => 'limit',
                    'PROPERTY_NAME' => 'Тип',
                    'PROPERTY_CODE' => 'TYPE',
                    'PROPERTY_SORT' => 100,
                ),
            764 =>
                array(
                    'ID' => 764,
                    'PROPERTY_ID' => 2489,
                    'VALUE' => 'связь',
                    'DEF' => 'N',
                    'SORT' => 500,
                    'XML_ID' => 'logic',
                    'TMP_ID' => NULL,
                    'EXTERNAL_ID' => 'logic',
                    'PROPERTY_NAME' => 'Тип',
                    'PROPERTY_CODE' => 'TYPE',
                    'PROPERTY_SORT' => 100,
                ),
            762 =>
                array(
                    'ID' => 762,
                    'PROPERTY_ID' => 2489,
                    'VALUE' => 'тогда',
                    'DEF' => 'N',
                    'SORT' => 500,
                    'XML_ID' => 'out',
                    'TMP_ID' => NULL,
                    'EXTERNAL_ID' => 'out',
                    'PROPERTY_NAME' => 'Тип',
                    'PROPERTY_CODE' => 'TYPE',
                    'PROPERTY_SORT' => 100,
                ),
            765 =>
                array(
                    'ID' => 765,
                    'PROPERTY_ID' => 2489,
                    'VALUE' => 'приоритет',
                    'DEF' => 'N',
                    'SORT' => 500,
                    'XML_ID' => 'priority',
                    'TMP_ID' => NULL,
                    'EXTERNAL_ID' => 'priority',
                    'PROPERTY_NAME' => 'Тип',
                    'PROPERTY_CODE' => 'TYPE',
                    'PROPERTY_SORT' => 100,
                ),
            760 =>
                array(
                    'ID' => 760,
                    'PROPERTY_ID' => 2489,
                    'VALUE' => 'сортировка',
                    'DEF' => 'N',
                    'SORT' => 500,
                    'XML_ID' => 'sort',
                    'TMP_ID' => NULL,
                    'EXTERNAL_ID' => 'sort',
                    'PROPERTY_NAME' => 'Тип',
                    'PROPERTY_CODE' => 'TYPE',
                    'PROPERTY_SORT' => 100,
                ),
        );
        $fromMethod = $object->buildSqlCondition($idCriterion, $idValue, $fieldCondition ?? "=", $field, $operation, $value);
        $actual = str_replace(array(" ","\r", "\n"), '', $fromMethod);

        $this->assertEquals($expected, $actual);
    }

}
