<?php

namespace ShoppinPal\YapepCommon\Queue;

use YapepBase\Config;
use YapepBase\Exception\ParameterException;

class QueueFactory
{
    const TYPE_SQS = 'sqs';
    const TYPE_KESTREL = 'kestrel';

    /**
     * @var array
     */
    protected static $queues = [];

    /**
     * @param string $configName
     *
     * @return IQueue
     *
     * @throws ParameterException if the configured queue type is invalid
     */
    public static function getQueue($configName)
    {
        if (isset(static::$queues[$configName])) {
            return static::$queues[$configName];
        }

        $config = Config::getInstance();

        $type = $config->get('commonResource.queue.' . $configName . '.type');

        switch ($type) {
            case self::TYPE_SQS:
                $queue = new SqsQueue($configName);
                break;

            case self::TYPE_KESTREL:
                $queue = new KestrelQueue($configName);
                break;

            default:
                throw new ParameterException('Invalid queue type: ' . $type);
        }

        static::$queues[$configName] = $queue;

        return $queue;
    }

    /**
     * Sets a queue instance for the specified config name.
     *
     * Should only be used while testing!
     *
     * @param string $configName
     * @param IQueue $queue
     *
     * @return void
     */
    public static function setClient($configName, IQueue $queue)
    {
        static::$queues[$configName] = $queue;
    }

}
