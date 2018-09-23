<?php
declare(strict_types = 1);

namespace ShoppinPal\YapepCommon\BusinessObject;

use ShoppinPal\YapepCommon\Exception\HealthCheckException;
use ShoppinPal\YapepCommon\HealthCheck\DbHealthCheck;
use ShoppinPal\YapepCommon\HealthCheck\HttpHealthCheck;
use ShoppinPal\YapepCommon\HealthCheck\IHealthCheck;
use ShoppinPal\YapepCommon\HealthCheck\S3HealthCheck;
use ShoppinPal\YapepCommon\HealthCheck\SqsHealthCheck;
use ShoppinPal\YapepCommon\HealthCheck\StorageHealthCheck;
use YapepBase\BusinessObject\BoAbstract;
use YapepBase\Config;
use YapepBase\Exception\ConfigException;
use YapepBase\Exception\ParameterException;

/**
 * Health check business logic
 *
 * Supported config options:
 * * applicationCommon.healthCheck.definitions: <type> => <className> format definitions of health checks for overriding existing checks or adding new ones
 * * applicationCommon.healthCheck.checks: the list of checks in [<type> => [<name> => <options>]...] format
 */
class HealthCheckBo extends BoAbstract
{
    protected const BUILT_IN_HEALTH_CHECKS = [
        'db'      => DbHealthCheck::class,
        'http'    => HttpHealthCheck::class,
        's3'      => S3HealthCheck::class,
        'sqs'     => SqsHealthCheck::class,
        'storage' => StorageHealthCheck::class,
    ];

    /**
     * @throws ConfigException
     */
    public function performHealthCheck(bool &$haveErrors = false): array
    {
        $healthCheckConfig = $this->getHealthCheckConfig();
        $healthChecks      = $this->getHealthChecks();
        $results           = [];

        foreach ($healthCheckConfig as $type => $checks) {
            $healthCheck = $this->getHealthCheck($healthChecks, $type);

            foreach ($checks as $name => $configOptions) {
                $fullName = $type . '.' . $name;

                if (!is_array($configOptions)) {
                    throw new ConfigException(
                        'Invalid health check config for ' . $fullName . '. The config should be an array.'
                    );
                }

                $haveErrors |= $this->performSingleHealthCheck($type, $name, $configOptions, $healthCheck, $results);
            }
        }

        return $results;
    }

    /**
     * @throws ConfigException
     */
    protected function getHealthChecks(): array
    {
        return array_merge(
            self::BUILT_IN_HEALTH_CHECKS,
            Config::getInstance()->get('applicationCommon.healthCheck.definitions', [])
        );
    }

    /**
     * @throws ConfigException
     */
    protected function getHealthCheckConfig(): array
    {
        $healthCheckConfig = Config::getInstance()->get('applicationCommon.healthCheck.checks');

        if (!is_array($healthCheckConfig)) {
            throw new ConfigException('The "applicationCommon.healthCheck.checks" should contain an array');
        }

        return $healthCheckConfig;
    }

    /**
     * @throws ConfigException
     */
    protected function getHealthCheck(array $healthChecks, string $type): IHealthCheck
    {
        if (!isset($healthChecks[$type])) {
            throw new ConfigException('Undefined health check type: ' . $type);
        }

        $healthCheckClass = $healthChecks[$type];

        if (!class_exists($healthChecks[$type])) {
            throw new ConfigException('Non-existing health check class: ' . $healthCheckClass);
        }

        $healthCheck = new $healthCheckClass();

        if (!($healthCheck instanceof IHealthCheck)) {
            throw new ConfigException(
                'The health check class ' . $healthCheckClass . ' does not implement IHealthCheck'
            );
        }

        return $healthCheck;
    }

    /**
     * @throws ConfigException
     */
    protected function performSingleHealthCheck(
        string $type,
        string $name,
        array $configOptions,
        IHealthCheck $healthCheck,
        array &$results
    ): bool {
        $error = false;
        $fullName = $type . '.' . $name;

        try {
            $healthCheck->checkServiceHealth($configOptions);

            $results[$type][$name] = [
                'status' => 'OK',
                'error'  => null,
            ];
        } catch (ParameterException $e) {
            throw new ConfigException(
                'Invalid health check config for ' . $fullName . ': ' . json_encode($configOptions), 0, $e
            );
        } catch (HealthCheckException $e) {
            $results[$type][$name] = [
                'status' => 'ERROR',
                'error'  => $e->getMessage(),
            ];

            $error = true;
        }

        return $error;
    }

}
