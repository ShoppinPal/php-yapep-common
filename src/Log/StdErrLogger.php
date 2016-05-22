<?php
namespace ShoppinPal\YapepCommon\Log;

use ShoppinPal\YapepCommon\Log\LogLevelHelper;
use YapepBase\Config;
use YapepBase\Log\LoggerAbstract;
use YapepBase\Log\Message;
use YapepBase\Log\Message\IMessage;

/**
 * Logger that echoes the log messages. All fields are going to be JSON encoded in the message.
 */
class StdErrLogger extends LoggerAbstract
{

    /**
     * The minimum log level to handle.
     *
     * @var int
     */
    protected $minimumLogLevel;

    /**
     * The log level helper instance.
     *
     * @var LogLevelHelper
     */
    protected $logLevelHelper;

    /**
     * @throws \YapepBase\Exception\ConfigException
     */
    public function __construct()
    {
        $this->minimumLogLevel = (int)Config::getInstance()->get('system.echoLogger.minimumLogLevel', LOG_INFO);
        $this->logLevelHelper  = new LogLevelHelper();
    }

    /**
     * Logs the message
     *
     * @param \YapepBase\Log\Message\IMessage $message The message to log.
     *
     * @return void
     */
    protected function logMessage(IMessage $message)
    {
        if ($message->getPriority() <= $this->minimumLogLevel) {
            $handle = fopen('php://stderr', 'a');
            fwrite(
                $handle,
                sprintf(
                    "%s %s(%d) [%s]: %s %s\n",
                    date('d-M-Y H:i:s'),
                    str_pad($this->logLevelHelper->getTextFromLevel($message->getPriority()), 5),
                    $message->getPriority(),
                    str_pad($message->getTag(), 20),
                    $message->getMessage(),
                    (count($message->getFields()) > 0 ? sprintf(
                        '(DATA: "%s")',
                        json_encode($message->getFields())
                    ) : '')
                )
            );
            fclose($handle);
        }
    }
}
