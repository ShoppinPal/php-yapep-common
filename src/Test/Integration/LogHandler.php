<?php
declare(strict_types = 1);

namespace ShoppinPal\YapepCommon\Test\Integration;

use YapepBase\Config;
use YapepBase\File\FileHandlerPhp;

class LogHandler
{
    public function initLogs(array $resourceNames)
    {
        $config = Config::getInstance();
        foreach ($resourceNames as $resourceName) {
            $errorLogPath = $config->get('resource.log.' . $resourceName . '.path');

            $fileHandler = new FileHandlerPhp();

            if ($fileHandler->checkIsPathExists($errorLogPath)) {
                $fileHandler->remove($errorLogPath);
            }
        }
    }

    public function getLoggedEntries(string $logResourceName): array
    {
        $logPath = Config::getInstance()->get('resource.log.' . $logResourceName . '.path');
        $logEntries = (new FileHandlerPhp())->getAsString($logPath);

        if (empty($logEntries)) {
            return array();
        }

        $loggedEntries = array();
        foreach (explode(PHP_EOL, trim($logEntries, PHP_EOL)) as $logEntry) {
            $loggedEntries[] = json_decode($logEntry, true);
        }
        return $loggedEntries;
    }
}
