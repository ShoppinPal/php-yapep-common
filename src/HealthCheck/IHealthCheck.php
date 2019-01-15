<?php
declare(strict_types = 1);

namespace ShoppinPal\YapepCommon\HealthCheck;

use ShoppinPal\YapepCommon\Exception\HealthCheckException;
use YapepBase\Exception\ParameterException;

/**
 * HealthCheck interface.
 *
 * Health Check classes MUST NOT have state or the checkServiceHealth method MUST reset the class state.
 */
interface IHealthCheck
{
    /**
     * Performs a health check using the specified config options.
     *
     * A health check MUST throw a ParameterException if the config is not correct, or a HealthCheckException if the
     * health check failed. A health check SHOULD handle all exceptions that are reasonably expected to get thrown
     * during the check, so only these 2 exception types SHOULD be thrown.
     *
     * @throws ParameterException   If the config options are incorrect
     * @throws HealthCheckException If the health check failed
     */
    public function checkServiceHealth(array $configOptions): void;
}
