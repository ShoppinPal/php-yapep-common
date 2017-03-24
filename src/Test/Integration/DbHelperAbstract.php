<?php
declare(strict_types = 1);

namespace ShoppinPal\YapepCommon\Test\Integration;


use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use YapepBase\Database\DbConnection;

abstract class DbHelperAbstract
{
    const ORDER_DIRECTION_ASCENDING = 'ASC';
    const ORDER_DIRECTION_DESCENDING = 'DESC';

    /**
     * @return DbConnection
     */
    abstract public function getDbConnection();


    public function assertTableEntryCount(string $tableName, int $expectedEntryCount)
    {
        $query = '
            SELECT
                COUNT(*)
            FROM
                ' . $tableName . '
        ';

        $entryCount = $this->getDbConnection()->query($query)->fetchColumn();

        Assert::assertEquals($expectedEntryCount, $entryCount);
    }

    /**
     * @param string    $tableName
     * @param TableNode $expectedResult
     * @param array     $order            Array where the key is the field name and the value is the direction
     *                                    {@uses self::ORDER_DIRECTION_*}
     */
    public function assertTableContents(string $tableName, TableNode $expectedResult, array $order)
    {
        $expectedRows = $expectedResult->getHash();

        $fields = array_keys($expectedRows[0]);

        $orderByFields = [];
        foreach ($order as $field => $direction) {
            $orderByFields[] = $field . ' ' . $direction;
        }

        $query = '
            SELECT
                ' . implode(', ', $fields) . '
            FROM
                ' . $tableName . '
            ORDER BY
                ' . implode(', ', $orderByFields) . '
        ';
        $entries = $this->getDbConnection()->query($query)->fetchAll();
        $this->formatArray($expectedRows);

        Assert::assertEquals($expectedRows, $entries);
    }

    /**
     * @param array  $array
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getFromArray(array $array, string $key, $default = null)
    {
        $result = array_key_exists($key, $array) ? $array[$key] : $default;

        return is_array($result) ? $result : $this->getFormattedValue($result);
    }

    public function getTimeStampGivenDaysBefore(int $dayCount): int
    {
        return $this->getCurrentTimestamp() - (60 * 60 * 24 * $dayCount);
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
        elseif (is_numeric($value)) {
            return $value + 0;
        }
        elseif (is_string($value)) {
            if (preg_match('#^<DATE: (.*)>$#', $value, $matches)) {
                $currentTimestamp = $this->getCurrentTimestamp();
                $time = $matches[1] == 'NOW' ? $currentTimestamp : strtotime($matches[1], $currentTimestamp);
                return date('Y-m-d', $time);
            }
            elseif (preg_match('#^<DATETIME: (.*)>$#', (string)$value, $matches)) {
                $currentTimestamp = $this->getCurrentTimestamp();
                $time = $matches[1] == 'NOW' ? $currentTimestamp : strtotime($matches[1], $currentTimestamp);
                return date('Y-m-d H:i:s', $time);
            }
        }


        return $value;
    }

    protected function getCurrentTimestamp(): int
    {
        return time();
    }
}
