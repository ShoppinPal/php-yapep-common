<?php
declare(strict_types = 1);

namespace ShoppinPal\YapepCommon\Test\Integration;

use YapepBase\Config;
use YapepBase\ErrorHandler\ErrorHandlerHelper;
use YapepBase\File\FileHandlerPhp;

class ErrorLogHandler
{
    /**
     * @var array
     */
    protected $ignoredErrors = [];


    public function ignoreError(int $errorCode, string $messagePrefix)
    {
        $errorHandlerHelper = new ErrorHandlerHelper();
        $message = '[' . $errorHandlerHelper->getPhpErrorLevelDescription($errorCode) . '(' . $errorCode . ')]: '
            . $messagePrefix;

        $this->ignoredErrors[] = $message;
    }

    public function getLoggedErrors(): array
    {
        $errorLogPath = Config::getInstance()->get('resource.log.error.path');

        $fileHandlerPhp = new FileHandlerPhp();

        if (!$fileHandlerPhp->checkIsPathExists($errorLogPath)) {
            return [];
        }

        $errorString = $fileHandlerPhp->getAsString($errorLogPath, 0);

        if (empty($errorString)) {
            return [];
        }
        $errors = [];
        foreach (explode(PHP_EOL, $errorString) as $error) {
            $errorMessage = json_decode($error, true)['message'];
            if (empty($errorMessage)) {
                continue;
            }

            if (!$this->isErrorIgnored($errorMessage)) {
                $errors[] = $errorMessage;
            }
        }

        return $errors;
    }


    protected function isErrorIgnored(string $errorMessage): bool
    {
        foreach ($this->ignoredErrors as $ignoredError) {
            if (mb_strpos($errorMessage, $ignoredError) === 0) {
                return true;
            }
        }

        return false;
    }
}
