<?php
declare(strict_types=1);

namespace Shoppinpal\YapepCommon\ErrorHandler;

use Sentry\State\HubInterface;
use YapepBase\ErrorHandler\IErrorHandler;

class SentryErrorHandler implements IErrorHandler
{
    /** @var HubInterface */
    protected $sentryClientHub;

    public function __construct(HubInterface $sentryClientHub)
    {
        $this->sentryClientHub = $sentryClientHub;
    }

    public function handleError($errorLevel, $message, $file, $line, $context, $errorId, array $backTrace = array())
    {
        $this->handleException(new \ErrorException($message, 0, $errorLevel, $file, $line), $errorId);
    }

    public function handleException($exception, $errorId)
    {
        $this->sentryClientHub->captureException($exception);
    }

    public function handleShutdown($errorLevel, $message, $file, $line, $errorId)
    {
        $this->handleException(new \ErrorException($message, 0, $errorLevel, $file, $line), $errorId);
    }

}
