<?php
namespace ShoppinPal\YapepCommon\Log;

use DateTime;

class JsonFormatter extends \Monolog\Formatter\JsonFormatter
{

    public function format(array $record)
    {
        return parent::format($this->getReformattedRecord($record));
    }

    protected function formatBatchJson(array $records)
    {
        return parent::formatBatchJson(array_map([$this, 'getReformattedRecord'], $records));
    }

    protected function getReformattedRecord(array $record)
    {
        return array_merge(
            $record['context'],
            [
                'msg'        => $record['message'],
                'channel'    => $record['channel'],
                'level'      => $record['level'],
                'level_name' => $record['level_name'],
                'time'       => $record['datetime'],
                'extra'      => $record['extra'],
                'v'          => 0,
            ]
        );
    }
}
