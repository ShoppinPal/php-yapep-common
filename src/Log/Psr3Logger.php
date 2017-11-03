<?php
namespace ShoppinPal\YapepCommon\Log;

use Psr\Log\LoggerInterface;
use ShoppinPal\YapepCommon\Helper\DependencyInjectionHelper;
use YapepBase\Config;
use YapepBase\Log\LoggerAbstract;
use YapepBase\Log\Message\IMessage;

class Psr3Logger extends LoggerAbstract
{

    const APPLICATION_UNKNOWN = 'UNKNOWN';
    const ENVIRONMENT_UNKNOWN = 'UNKNOWN';

    /** @var string */
    protected $programName;

    /** @var string */
    protected $applicationName;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;

        $this->programName = Config::getInstance()->get('system.project.name');
    }

    /**
     * @return LoggerInterface
     */
    public function getPsr3Logger()
    {
        return $this->logger;
    }

    /**
     * @inheritdoc
     */
    protected function logMessage(IMessage $message)
    {
        $environment = DependencyInjectionHelper::hasEnvironment()
            ? DependencyInjectionHelper::getEnvironment()
            : self::ENVIRONMENT_UNKNOWN;

        $context = [
            'program'     => $this->programName,
            'application' => $this->getApplicationName(),
            'hostname'    => php_uname('n'),
            'language'    => 'php',
            'environment' => $environment,
            'pid'         => getmypid(),
            'sapi'        => PHP_SAPI,
            'tag'         => $message->getTag(),
            'fields'      => $message->getFields(),
        ];


        switch ($message->getPriority()) {
            case LOG_DEBUG:
                $this->logger->debug($message->getMessage(), $context);
                break;

            case LOG_INFO:
                $this->logger->info($message->getMessage(), $context);
                break;

            case LOG_NOTICE:
                $this->logger->notice($message->getMessage(), $context);
                break;

            case LOG_WARNING:
                $this->logger->warning($message->getMessage(), $context);
                break;

            case LOG_ERR:
                $this->logger->error($message->getMessage(), $context);
                break;

            case LOG_CRIT:
                $this->logger->critical($message->getMessage(), $context);
                break;

            case LOG_ALERT:
                $this->logger->alert($message->getMessage(), $context);
                break;

            case LOG_EMERG:
                $this->logger->emergency($message->getMessage(), $context);
                break;
        }
    }

    /**
     * @return string
     */
    protected function getApplicationName()
    {
        if (null !== $this->applicationName) {
            return $this->applicationName;
        }

        $applicationName = Config::getInstance()->get('application.name', self::APPLICATION_UNKNOWN);

        if (self::APPLICATION_UNKNOWN != $applicationName)
        {
            $this->applicationName = $applicationName;
        }

        return $applicationName;
    }
}
