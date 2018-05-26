<?php
declare(strict_types = 1);

namespace ShoppinPal\YapepCommon\BusinessObject;

use ShoppinPal\YapepCommon\Exception\HealthCheckException;
use ShoppinPal\YapepCommon\HealthCheck\DbHealthCheck;
use ShoppinPal\YapepCommon\HealthCheck\IHealthCheck;
use ShoppinPal\YapepCommon\HealthCheck\SqsHealthCheck;
use ShoppinPal\YapepCommon\HealthCheck\StorageHealthCheck;
use YapepBase\BusinessObject\BoAbstract;
use YapepBase\Config;
use YapepBase\Exception\ConfigException;
use YapepBase\Exception\ParameterException;

class HealthCheckBo extends BoAbstract
{
    protected const BUILT_IN_HEALTH_CHECKS = [
        'db'      => DbHealthCheck::class,
        'storage' => StorageHealthCheck::class,
        'sqs'     => SqsHealthCheck::class,
    ];

    /**
     * @throws ConfigException
     */
    public function performHealthCheck(bool &$haveErrors = false): array
    {
        $config            = Config::getInstance();
        $healthCheckConfig = $config->get('applicationCommon.healthCheck.checks');
        $results           = [];

        if (!is_array($healthCheckConfig)) {
            throw new ConfigException('The "applicationCommon.healthCheck.checks" should contain an array');
        }

        $healthChecks = array_merge(
            self::BUILT_IN_HEALTH_CHECKS,
            $config->get('applicationCommon.healthCheckDefinitions', [])
        );

        foreach ($healthCheckConfig as $type => $checks) {
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

            foreach ($checks as $name => $configOptions) {
                $fullName = $type . '.' . $name;

                if (!is_array($configOptions)) {
                    throw new ConfigException(
                        'Invalid health check config for ' . $fullName . '. The config should be an array.'
                    );
                }

                try {
                    $healthCheck->checkServiceHealth($configOptions);

                    $results[$type][$name] = [
                        'status' => 'OK',
                        'error'  => null,
                    ];
                } catch (ParameterException $e) {
                    throw new ConfigException(
                        'Invalid health check config for ' . $fullName . ': ' . json_encode($configOptions),
                        0,
                        $e
                    );
                } catch (HealthCheckException $e) {
                    $results[$type][$name] = [
                        'status' => 'ERROR',
                        'error'  => $e->getMessage(),
                    ];
                }
            }
        }

        return $results;
    }
}
