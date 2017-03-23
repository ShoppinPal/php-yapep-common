<?php
declare(strict_types = 1);

namespace ShoppinPal\YapepCommon\Test\Integration;


use Behat\Gherkin\Node\TableNode;
use YapepBase\Helper\HelperAbstract;


class TableNodeHelper extends HelperAbstract
{

    public function addTableNodeColumn(TableNode $table, string $columnName, array $columnValues): TableNode
    {
        $originalArray = $table->getTable();

        $rowNum = 0;
        foreach ($originalArray as $index => $row) {
            if ($rowNum == 0) {
                $originalArray[$index][] = $columnName;
            }
            else {
                $originalArray[$index][] = (string)$columnValues[$rowNum - 1];
            }

            $rowNum++;
        }

        return new TableNode($originalArray);
    }


    public function createTableNodeByArrayOfArrays(array $array): TableNode
    {
        $inputArray = array();

        $rowNum = 0;
        foreach ($array as $row) {
            if ($rowNum == 0) {
                $inputArray[] = array_keys($row);
            }

            $inputArray[] = array_values($row);
            $rowNum++;
        }
        $tableNode = new TableNode($inputArray);

        return $tableNode;
    }
}
