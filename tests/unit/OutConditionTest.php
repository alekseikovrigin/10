<?php

namespace Inetris\Nocode;

class OutConditionTest extends BitrixTestCase
{
    public function handleDataProvider(): array
    {
        $criterion["SORT"] = array(
            'PROPERTY_TYPE_CODE' => 'integer',
            'PROP' =>
                array(
                    'CODE_IN' =>
                        array(
                            'VALUE' => 'SORT',
                        ),
                    'CODE_OUT' =>
                        array(
                            'VALUE' => 'SORT',
                        ),
                    'HANDLER' =>
                        array(
                            'VALUE' => NULL,
                        ),
                    'TYPE' =>
                        array(
                            'VALUE' => 3355,
                        ),
                ),
        );
        $operation[">"] = array(
            'NAME' => 'больше',
            'CODE' => '>',
            '~CODE' => '>',
            'PROP' =>
                array(
                    'OPER' =>
                        array(
                            '~VALUE' => '>',
                        ),
                    'PREF' =>
                        array(
                            'VALUE' => NULL,
                        ),
                    'OPERNOT' =>
                        array(
                            'VALUE' => '>=',
                        ),
                    'PREFNOT' =>
                        array(
                            'VALUE' => NULL,
                        ),
                    'OP_TYPE' =>
                        array(
                            'VALUE' =>
                                array(
                                    0 => 3356,
                                    1 => 3355,
                                ),
                        ),
                    'PROCESS' =>
                        array(
                            'VALUE' => NULL,
                            'VALUE_XML_ID' => NULL,
                        ),
                ),
        );

        return [
            [new DTO\ConditionDTO(3352, "out", $criterion["SORT"], $operation[">"], 500, null, 3385, null), [
                'filter' => [
                    3385 => [
                        'data' => ['>SORT' => 500],
                        'id' => 3385,
                        'parent_id' => 0
                    ]
                ],
            ]],
        ];
    }

    /**
     * @dataProvider handleDataProvider
     * @test
     */
    public function handleVariousScenarios($dto, $expected)
    {
        $arResult = array(
            'SORT_PARAM' => 'desc',
            'SORT' => 1,
            'getFirstCatalog($_SERVER[\'REQUEST_URI\'])' => 'nocode_demo',
            'getHost($_SERVER[\'HTTP_REFERER\'])' => 'bitrix22.ru',
        );
        $arTypes = array(
            3354 =>
                array(
                    'ID' => 3354,
                    '~ID' => 3354,
                    'IBLOCK_ID' => 646,
                    '~IBLOCK_ID' => 646,
                    'NAME' => 'строка',
                    '~NAME' => 'строка',
                    'CODE' => 'string',
                    '~CODE' => 'string',
                    'PROP' =>
                        array(),
                ),
            3355 =>
                array(
                    'ID' => 3355,
                    '~ID' => 3355,
                    'IBLOCK_ID' => 646,
                    '~IBLOCK_ID' => 646,
                    'NAME' => 'число',
                    '~NAME' => 'число',
                    'CODE' => 'integer',
                    '~CODE' => 'integer',
                    'PROP' =>
                        array(),
                ),
            3356 =>
                array(
                    'ID' => 3356,
                    '~ID' => 3356,
                    'IBLOCK_ID' => 646,
                    '~IBLOCK_ID' => 646,
                    'NAME' => 'дата/время',
                    '~NAME' => 'дата/время',
                    'CODE' => 'time',
                    '~CODE' => 'time',
                    'PROP' =>
                        array(),
                ),
        );

        $strategy = Service\ConditionStrategyFactory::create($dto->type);
        $actual = $strategy->handle($dto, $arTypes, $arResult);

        $this->assertEquals($expected, $actual);
    }

}
