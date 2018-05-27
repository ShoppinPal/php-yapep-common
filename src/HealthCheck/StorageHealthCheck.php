<?php
declare(strict_types = 1);

namespace ShoppinPal\YapepCommon\HealthCheck;

use ShoppinPal\YapepCommon\Exception\HealthCheckException;
use ShoppinPal\YapepCommon\Storage\StorageFactory;
use YapepBase\Exception\ConfigException;
use YapepBase\Exception\ParameterException;
use YapepBase\Exception\StorageException;
use YapepBase\Storage\StorageAbstract;

class StorageHealthCheck implements IHealthCheck
{
    public function checkServiceHealth(array $configOptions): void
    {
        if (!isset($configOptions['connectionName'])) {
            throw new ParameterException('Missing connectionName from config options for storage health check');
        }

        $testKey        = $configOptions['healthCheckKey'] ?? 'ShoppinPalCommonHealthCheckTestKey';
        $connectionName = $configOptions['connectionName'];

        try {
            $errorPrefix = 'Error while creating connection';
            $connection  = $this->getConnection($connectionName);

            $errorPrefix = 'Error while checking the operation of the storage';
            $this->checkOperation($connection, $testKey);
        } catch (StorageException | ConfigException | ParameterException $e) {
            throw new HealthCheckException(
                'Storage health check failed for ' . $connectionName . '. '
                    . $errorPrefix . ': ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @throws ConfigException
     * @throws StorageException
     */
    protected function getConnection(string $connectionName): StorageAbstract
    {
        return StorageFactory::get($connectionName);
    }

    /**
     * @throws StorageException
     * @throws ParameterException
     */
    protected function checkOperation(StorageAbstract $connection, string $testKey): void
    {
        $expected = 'HealthCheckData_' . microtime(true);
        $connection->set($testKey, $expected);

        $actual = $connection->get($testKey);

        $connection->delete($testKey);

        if ($actual !== $expected) {
            throw new StorageException(
                'The read back data is invalid. Expected ' . json_encode($expected) . '. Got ' . json_encode($expected)
            );
        }
    }
}
