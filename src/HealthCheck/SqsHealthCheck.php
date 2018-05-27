<?php
declare(strict_types = 1);

namespace ShoppinPal\YapepCommon\HealthCheck;

use ShoppinPal\YapepCommon\Exception\HealthCheckException;
use ShoppinPal\YapepCommon\Queue\Sqs;
use ShoppinPal\YapepCommon\Queue\SqsFactory;
use ShoppinPal\YapepCommon\Queue\SqsQueueConfigHandler;
use YapepBase\Exception\ConfigException;
use YapepBase\Exception\Exception;
use YapepBase\Exception\ParameterException;

class SqsHealthCheck implements IHealthCheck
{
    public function checkServiceHealth(array $configOptions): void
    {
        if (!isset($configOptions['connectionName'])) {
            throw new ParameterException('Missing connectionName from config options for storage health check');
        }

        if (!isset($configOptions['queueName'])) {
            throw new ParameterException('Missing queueName from config options for storage health check');
        }

        $connectionName  = $configOptions['connectionName'];
        $queueConfigName = $configOptions['queueName'];

        try {
            $errorPrefix = 'Error while creating connection';
            $connection  = $this->getConnection($connectionName);

            $errorPrefix = 'Error while selecting the current date from the connection';
            $this->checkOperation($connection, $queueConfigName);
        } catch (ConfigException | Exception $e) {
            throw new HealthCheckException(
                'SQS health check failed for ' . $connectionName . '. '
                . $errorPrefix . ': ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @throws ConfigException
     */
    protected function getConnection(string $connectionName): Sqs
    {
        return SqsFactory::getQueue($connectionName);
    }

    /**
     * @throws Exception
     */
    protected function checkOperation(Sqs $connection, string $queueConfigName): void
    {
        $configHandler = new SqsQueueConfigHandler();
        $queueName     = $configHandler->getNameForQueue($queueConfigName);
        $result        = $connection->getQueueUrlAndCreateIfNotExists($queueName, $queueConfigName);

        if (empty($result)) {
            throw new Exception('The returned queue URL is empty');
        }
    }
}
