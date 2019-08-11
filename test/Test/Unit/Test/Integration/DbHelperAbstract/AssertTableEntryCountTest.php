<?php
declare(strict_types = 1);


namespace Test\Unit\Test\Integration\DbHelperAbstract;

use Mockery;

class AssertTableEntryCountTest extends DbHelperAbstractTestAbstract
{

    public function testWhenRequirementsMet_nothingShouldHappen(): void
    {
        $query = '
            SELECT
                COUNT(*)
            FROM
                table_name
        ';

        $this->expectFetchColumn($query, 2);
        $this->object->assertTableEntryCount('table_name', 2);
    }

    public function testWhenRequirementsNotMet_shouldThrowException(): void
    {
        $query = '
            SELECT
                COUNT(*)
            FROM
                table_name
        ';

        $this->expectFetchColumn($query, 0);
        $this->expectException('\PhpUnit\Framework\ExpectationFailedException');
        $this->object->assertTableEntryCount('table_name', 2);
    }


    protected function expectFetchColumn(string $query, $expectedResult): void
    {
        $dbResultMock = Mockery::mock('\YapepBase\Database\DbResult')
            ->shouldReceive('fetchColumn')
            ->once()
            ->andReturn($expectedResult)
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
