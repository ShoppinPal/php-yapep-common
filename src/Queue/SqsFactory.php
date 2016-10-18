<?php

namespace ShoppinPal\YapepCommon\Queue;

use YapepBase\Exception\ConfigException;

class SqsFactory
{

    /**
     * @var array
     */
    protected static $queues = [];

    /**
     * Returns an SQS instance by the specified config name.
     *
     * @param string $configName
     *
     * @return Sqs
     *
     * @throws ConfigException If the specified sqs instance is not configured properly.
     */
    public static function getQueue($configName)
    {
        if (!isset(static::$queues[$configName])) {
            static::$queues[$configName] = new Sqs($configName);
        }

        return static::$queues[$configName];
    }

    /**
     * Sets a queue instance for the specified config name.
     *
     * Should only be used while testing!
     *
     * @param string $configName
     * @param Sqs    $queue
     *
     * @return void
     */
    public static function setQueue($configName, Sqs $queue)
    {
        static::$queues[$configName] = $queue;
    }
}
