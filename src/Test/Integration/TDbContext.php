<?php

namespace ShoppinPal\YapepCommon\Test\Integration;


use PHPUnit_Framework_Assert;
use YapepBase\Database\DbConnection;

trait TDbContext
{

    /**
     * @return DbConnection
     */
    abstract public function getDbConnection();

    /**
     * @Then the :tableName table should be empty
     */
    public function theTableShouldBeEmpty($tableName)
    {
        $query
            = '
            SELECT
                *
            FROM
                ' . $tableName . '
            LIMIT 1
        ';
        $result = $this->getDbConnection()->query($query)->fetch();

        PHPUnit_Framework_Assert::assertFalse($result, 'The table "' . $tableName . '" is not empty!"');
    }


    /**
     * @Then the :tableName should contain :rowCount rows
     */
    public function theTableShouldContainRows($tableName, $expectedRowCount)
    {
        $query
            = '
            SELECT
                COUNT(*)
            FROM
                ' . $tableName . '
        ';
        $rowCount = $this->getDbConnection()->query($query)->fetchColumn();

        PHPUnit_Framework_Assert::assertEquals($expectedRowCount, $rowCount);
    }
}
