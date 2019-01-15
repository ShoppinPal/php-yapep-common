<?php
declare(strict_types = 1);

namespace ShoppinPal\YapepCommon\HealthCheck;

use Aws\S3\Exception\S3Exception;
use ShoppinPal\YapepCommon\Exception\HealthCheckException;
use ShoppinPal\YapepCommon\FileStorage\S3;
use ShoppinPal\YapepCommon\FileStorage\S3Factory;
use YapepBase\Exception\ConfigException;
use YapepBase\Exception\ParameterException;

class S3HealthCheck implements IHealthCheck
{
    public function checkServiceHealth(array $configOptions): void
    {
        if (!isset($configOptions['connectionName'])) {
            throw new ParameterException('Missing connectionName from config options for S3 health check');
        }

        $testKey        = $configOptions['healthCheckKey'] ?? 'ShoppinPalCommonHealthCheckTestKey';
        $connectionName = $configOptions['connectionName'];

        try {
            $errorPrefix = 'Error while creating connection';
            $connection  = $this->getConnection($connectionName);

            $errorPrefix = 'Error while checking the operation of the storage';
            $this->checkOperation($connection, $testKey);
        } catch (S3Exception | ConfigException | ParameterException | HealthCheckException $e) {
            throw new HealthCheckException(
                'S3 health check failed for ' . $connectionName . '. '
                . $errorPrefix . ': ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @throws ConfigException
     */
    protected function getConnection(string $connectionName): S3
    {
        return S3Factory::getClient($connectionName);
    }

    /**
     * @throws HealthCheckException
     * @throws ParameterException
     */
    protected function checkOperation(S3 $connection, string $testKey): void
    {
        $expected = 'HealthCheckData_' . microtime(true);
        $connection->putObject($testKey, $expected);

        $actual = $connection->getObject($testKey);

        $connection->deleteObject($testKey);

        if ((string)$actual->get('Body') !== $expected) {
            throw new HealthCheckException(
                'The read back data is invalid. Expected ' . json_encode($expected) . '. Got ' . json_encode($actual)
            );
        }
    }
}
