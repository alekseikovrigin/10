<?php

namespace Inetris\Nocode;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentTypeException;

class HierarchicalFilterTest extends BitrixTestCase
{
    public function hierarchyProvider(): array
    {
        return [
            [
                [
                    1 => ['id' => 1, 'parent_id' => 0, 'data' => ['LOGIC' => 'OR']],
                    2 => ['id' => 2, 'parent_id' => 1, 'data' => ['LOGIC' => 'AND']],
                    3 => ['id' => 3, 'parent_id' => 1, 'data' => ['>=DATE_CREATE' => '15.11.2023 00:00:00']],
                    4 => ['id' => 4, 'parent_id' => 2, 'data' => ['>SORT' => '500']],
                    5 => ['id' => 5, 'parent_id' => 2, 'data' => ['<=SORT' => '700']]
                ],
                null,
                [
                    1 => [
                        'LOGIC' => 'OR',
                        2 => [
                            'LOGIC' => 'AND',
                            4 => ['>SORT' => '500'],
                            5 => ['<=SORT' => '700']
                        ],
                        3 => ['>=DATE_CREATE' => '15.11.2023 00:00:00']
                    ]
                ],
                'expectedException' => null,
            ],
            [
                [
                    1 => ['id' => 1, 'parent_id' => 0, 'data' => ['LOGIC' => 'OR']],
                    2 => ['id' => 2, 'parent_id' => 1, 'data' => ['LOGIC' => 'AND']],
                    3 => ['id' => 3, 'parent_id' => 1, 'data' => ['>=DATE_CREATE' => '15.11.2023 00:00:00']],
                    4 => ['id' => 4, 'parent_id' => 2, 'data' => ['>SORT' => '500']],
                    5 => ['id' => 5, 'parent_id' => 21, 'data' => ['<=SORT' => '700']]
                ],
                null,
                null,
                'expectedException' => \Exception::class,
            ],
            [
                [
                    1 => ['id' => 1, 'parent_id' => 0, 'data' => ['LOGIC' => 'OR']],
                    2 => ['id' => 2, 'parent_id' => 1, 'data' => ['LOGIC' => 'AND']],
                ],
                null,
                null,
                'expectedException' => \Exception::class,
            ]
        ];
    }

    /**
     * @dataProvider hierarchyProvider
     * @test
     */
    public function buildSqlCondition(array $elements, $parentId, $expected, $expectedException)
    {
        $object = new Service\QueryBuilder();

        if ($expectedException !== null) {
            $this->expectException($expectedException);
        }

        try {
            $actual = $object->buildHierarchy($elements);
        } catch (ArgumentNullException|ArgumentTypeException $e) {
        }

        if ($expectedException === null) {
            $this->assertEquals($expected, $actual, "Hierarchy does not match expected structure");
        }
    }

}
