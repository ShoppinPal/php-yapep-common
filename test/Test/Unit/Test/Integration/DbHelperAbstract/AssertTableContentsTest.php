<?php
declare(strict_types = 1);


namespace Test\Unit\Test\Integration\DbHelperAbstract;

use Mockery;
use ShoppinPal\YapepCommon\Test\Integration\DbHelperAbstract;
use ShoppinPal\YapepCommon\Test\Integration\TableNodeHelper;

class AssertTableContentsTest extends DbHelperAbstractTestAbstract
{

    public function testWhenRequirementsMet_nothingShouldHappen()
    {
        $query = '
            SELECT
                field1, field2
            FROM
                table_name
            ORDER BY
                field1 ASC
        ';
        $fetchResult = [
            [
                'field1' => 'value1',
                'field2' => 'value2'
            ],
            [
                'field1' => 'value12',
                'field2' => 'value22'
            ]
        ];

        $this->expectFetchAll($query, $fetchResult);
        $expectedResult = (new TableNodeHelper())->createTableNodeByArrayOfArrays($fetchResult);

        $this->object->assertTableContents('table_name', $expectedResult, ['field1' => DbHelperAbstract::ORDER_DIRECTION_ASCENDING]);
    }

    public function testWhenRequirementsNotMet_shouldThrowException()
    {
        $query = '
            SELECT
                field1, field2
            FROM
                table_name
            ORDER BY
                field1 ASC
        ';
        $fetchResult = [
            [
                'field1' => 'value1',
                'field2' => 'value2'
            ],
            [
                'field1' => 'value12',
                'field2' => 'value22'
            ]
        ];
        $differentExpectation = [
            [
                'field1' => 'different',
                'field2' => 'value2'
            ],
            [
                'field1' => 'value12',
                'field2' => 'value22'
            ]
        ];

        $this->expectFetchAll($query, $fetchResult);
        $expectedResult = (new TableNodeHelper())->createTableNodeByArrayOfArrays($differentExpectation);

        $this->expectException('\PhpUnit\Framework\ExpectationFailedException');
        $this->object->assertTableContents('table_name', $expectedResult, ['field1' => DbHelperAbstract::ORDER_DIRECTION_ASCENDING]);
    }


    protected function expectFetchAll(string $query, array $fetchResult)
    {
        $dbResultMock = Mockery::mock('\YapepBase\Database\DbResult')
            ->shouldReceive('fetchAll')
            ->once()
            ->andReturn($fetchResult)
            ->getMock();

        $dbConnection = Mockery::mock('\YapepBase\Database\DbConnection')
            ->shouldReceive('query')
            ->once()
            ->with($query)
            ->andReturn($dbResultMock)
            ->getMock();

        $this->expectGetDbConnection($dbConnection);
    }

}
