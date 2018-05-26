<?php
declare(strict_types = 1);

namespace ShoppinPal\YapepCommon\HealthCheck;

use ShoppinPal\YapepCommon\Exception\HealthCheckException;
use YapepBase\Database\DbConnection;
use YapepBase\Database\DbFactory;
use YapepBase\Database\MysqlConnection;
use YapepBase\Exception\DatabaseException;
use YapepBase\Exception\ParameterException;

class DbHealthCheck implements IHealthCheck
{
    public function checkServiceHealth(array $configOptions = []): void
    {
        if (!isset($configOptions['connectionName'])) {
            throw new ParameterException('Missing connectionName from config options for DB health check');
        }

        $connectionName = $configOptions['connectionName'];
        $checkRo        = $configOptions['checkRo'] ?? true;
        $checkRw        = $configOptions['checkRw'] ?? true;

        if (!$checkRo && !$checkRw) {
            throw new ParameterException(
                'Neither RO or RW connection checks are enabled for DB health check ' . $connectionName
            );
        }

        // Make sure there are no existing connections in the DB factory
        DbFactory::clearConnections();

        // RO must be checked first, as after an RW connection, the RW connection will always be returned
        if ($checkRo) {
            $this->performCheck($connectionName, DbFactory::TYPE_READ_ONLY);
        }

        if ($checkRw) {
            $this->performCheck($connectionName, DbFactory::TYPE_READ_WRITE);
        }
    }

    /**
     * @throws HealthCheckException
     */
    protected function performCheck(string $connectionName, string $connectionType): void
    {
        try {
            $errorPrefix = 'Error while creating connection';
            $connection  = $this->getConnection($connectionName, $connectionType);

            $errorPrefix = 'Error while selecting the current date from the connection';
            $this->checkOperation($connection);
        } catch (DatabaseException $e) {
            throw new HealthCheckException(
                'DB health check failed for ' . $connectionName . '/' . $connectionType . '. '
                . $errorPrefix . ': ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @throws DatabaseException
     */
    protected function getConnection(string $connectionName, string $connectionType): DbConnection
    {
        return DbFactory::getConnection($connectionName, $connectionType);
    }

    /**
     * @throws DatabaseException
     */
    protected function checkOperation(DbConnection $connection): void
    {
        if ($connection instanceof MysqlConnection) {
            $result = $connection->query('SELECT NOW()')->fetchColumn();

            if (empty($result)) {
                throw new DatabaseException('Received empty result for SELECT NOW() query');
            }
        } else {
            trigger_error(
                'The DB health check currently does not support connections of ' . get_class($connection)
                . '. The check may be incomplete.',
                E_USER_WARNING
            );
        }
    }
}
