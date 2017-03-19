<?php

namespace ShoppinPal\YapepCommon\Test\Integration;


use Behat\Gherkin\Node\TableNode;
use PHPUnit_Framework_Assert;
use YapepBase\Database\DbConnection;

abstract class DbHelperAbstract
{

    /**
     * @return DbConnection
     */
    abstract public function getDbConnection();

    /**
     * @param string $tableName
     * @param int    $expectedEntryCount
     */
    public function assertTableEntryCount($tableName, $expectedEntryCount)
    {
        $query
            = '
            SELECT
                COUNT(*)
            FROM
                ' . $tableName . '
        ';

        $entryCount = $this->getDbConnection()->query($query)->fetchColumn();

        PHPUnit_Framework_Assert::assertEquals($expectedEntryCount, $entryCount);
    }

    /**
     * @param string    $tableName
     * @param TableNode $table
     */
    public function assertTableContents($tableName, TableNode $table)
    {
        $expectedRows = $table->getHash();

        $fields = array_keys($expectedRows[0]);

        $query
            = '
            SELECT
                ' . implode(', ', $fields) . '
            FROM
                ' . $tableName . '
        ';
        $entries = $this->getDbConnection()->query($query)->fetchAll();
        $this->formatArray($expectedRows);

        PHPUnit_Framework_Assert::assertEquals($expectedRows, $entries);
    }

    /**
     * @param array  $array
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getFromArray(array $array, $key, $default = null)
    {
        $result = array_key_exists($key, $array) ? $array[$key] : $default;

        return is_array($result) ? $result : $this->getFormattedValue($result);
    }

    /**
     * @param int $dayCount
     *
     * @return int
     */
    public function getTimeStampGivenDaysBefore($dayCount)
    {
        return time() - (60 * 60 * 24 * $dayCount);
    }

    /**
     * @param array $arrayToFormat
     */
    public function formatArray(array &$arrayToFormat)
    {
        foreach ($arrayToFormat as $key => &$value) {
            if (is_array($value)) {
                $this->formatArray($value);
            }
            else {
                $value = $this->getFormattedValue($value);
            }
        }
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function getFormattedValue($value)
    {
        if ($value === '<null>') {
            return null;
        }
        elseif (preg_match('#^<DATE: (.*)>$#', $value, $matches)) {
            $time = $matches == 'NOW' ? time() : strtotime($matches[1]);
            return date('Y-m-d', $time);
        }
        elseif (preg_match('#^<DATETIME: (.*)>$#', $value, $matches)) {
            $time = $matches == 'NOW' ? time() : strtotime($matches[1]);
            return date('Y-m-d H:i:s', $time);
        }

        if (is_numeric($value)) {
            return ctype_digit($value) ? (int)$value : (float)$value;
        }

        return $value;
    }
}
