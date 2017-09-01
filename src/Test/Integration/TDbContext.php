<?php
declare(strict_types = 1);

namespace ShoppinPal\YapepCommon\Test\Integration;


use PHPUnit\Framework\Assert;
use YapepBase\Database\DbConnection;

trait TDbContext
{

    /**
     * @return DbConnection
     */
    abstract public function getDbConnection();


    protected function createEntity(string $tableName, array $rows)
    {
        foreach ($rows as $row) {
            $queryParams = [];
            $sets = [];

            foreach ($row as $columnName => $value) {
                $queryParams[$columnName] = $value;
                $sets[] = $columnName . ' = :' . $columnName;
            }

            $insert = '
                INSERT INTO
                    ' . $tableName . '
                SET
                    ' . implode(', ', $sets) . '
            ';

            $this->getDbConnection()->query($insert, $queryParams);
        }
    }

    /**
     * @Then the :tableName table should be empty
     */
    public function theTableShouldBeEmpty(string $tableName)
    {
        $query = '
            SELECT
                *
            FROM
                ' . $tableName . '
            LIMIT 1
        ';
        $result = $this->getDbConnection()->query($query)->fetch();

        Assert::assertFalse($result, 'The table "' . $tableName . '" is not empty!"');
    }


    /**
     * @Then the :tableName should contain :rowCount rows
     */
    public function theTableShouldContainRows(string $tableName, int $expectedRowCount)
    {
        $query = '
            SELECT
                COUNT(*)
            FROM
                ' . $tableName . '
        ';
        $rowCount = $this->getDbConnection()->query($query)->fetchColumn();

        Assert::assertEquals($expectedRowCount, $rowCount);
    }
}
