<?php
namespace ShoppinPal\YapepCommon\HealthCheck\Batch;

use YapepBase\Application;

trait TLastRunTimeCheck
{
    protected function updateHealthCheckFile($path)
    {
        $fileHandler = Application::getInstance()->getDiContainer()->getFileHandler();

        if (!$fileHandler->checkIsPathExists(basename($path))) {
            $fileHandler->makeDirectory(basename($path), 0755, true);
        }

        $fileHandler->write($path, time(), false, true);
    }
}
