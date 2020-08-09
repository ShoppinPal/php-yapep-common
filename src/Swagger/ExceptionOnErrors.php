<?php
declare(strict_types=1);

namespace ShoppinPal\YapepCommon\Swagger;

use Exception;
use Swagger\Analysis;

/**
 * Throws exception if there were any errors during swagger processing to allow CI to detect this
 */
class ExceptionOnErrors
{
    /**
     * @throws \ErrorException
     */
    public function __construct()
    {
        // Override the error handler in swagger to catch any errors
        set_error_handler(function ($errno, $errstr, $file, $line) {
            if (!(error_reporting() & $errno)) {
                // This error code is not included in error_reporting
                return;
            }

            throw new \ErrorException($errstr, 100, $errno, $file, $line);
        });
    }

    /**
     * @throws Exception
     */
    public function __invoke(Analysis $analysis)
    {
        if (!$analysis->validate()) {
            throw new Exception('Validation failed');
        }
    }
}
