<?php
namespace ShoppinPal\YapepCommon\Log;

use Monolog\Formatter\FormatterInterface;

class LineFormatter implements FormatterInterface
{

    public function format(array $record)
    {
        return sprintf(
            "%s %s(%d) [%s]: %s %s\n",
            date('d-M-Y H:i:s'),
            $record['level_name'],
            $record['level'],
            str_pad($record['context']['tag'], 20),
            $record['message'],
            (count($record['context']['fields']) > 0 ? sprintf(
                '(DATA: "%s")',
                json_encode($record['context']['fields'])
            ) : '')
        );
    }

    public function formatBatch(array $records)
    {
        $result = [];

        foreach ($records as $record)
        {
            $result[] = $this->format($record);
        }

        return implode("\n", $result);
    }

}
