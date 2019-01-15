<?php
declare(strict_types = 1);

namespace ShoppinPal\YapepCommon\Controller;

use ShoppinPal\YapepCommon\BusinessObject\HealthCheckBo;
use ShoppinPal\YapepCommon\Exception\RestException;
use YapepBase\Config;
use YapepBase\Exception\ConfigException;

class HealthCheckController extends RestApiController
{
    protected function getHeartbeat()
    {
        return ['heartbeat' => 'OK'];
    }

    /**
     * Runs the health check action
     *
     * Uses the system.project.name and application.name config options, and requires setting an API key as
     * the common.healthCheck.key config value
     *
     * @throws ConfigException
     * @throws RestException
     */
    protected function getHealthCheck()
    {
        $config = Config::getInstance();
        $apiKey = $config->get('common.healthCheck.key');

        if (empty($apiKey) || $apiKey !== $this->request->getServer('HTTP_X_API_KEY')) {
            throw new RestException(RestException::CODE_UNAUTHORIZED);
        }

        $healthCheckBo   = new HealthCheckBo();
        $appFullName     = $config->get('system.project.name') . '.' . $config->get('application.name');
        $haveErrors      = false;
        $detailedResults = $healthCheckBo->performHealthCheck($haveErrors);

        return [
            'status'          => $haveErrors ? 'ERROR' : 'HEALTHY',
            'appName'         => $appFullName,
            'detailedResults' => $detailedResults,
        ];
    }
}
