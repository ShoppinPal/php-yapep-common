<?php

namespace ShoppinPal\YapepCommon\Config;

use YapepBase\Exception\Exception;

abstract class ProjectConfigAbstract
{
    /**
     * @param string $configName
     * @param mixed  $default
     *
     * @return mixed
     *
     * @throws Exception If the value is not set and no default is provided.
     */
    public static function getEnvConfigValue($configName, $default = null)
    {
        if (false !== getenv($configName)) {
            return getenv($configName);
        }

        if (null === $default) {
            throw new Exception(
                'The config value ' . $configName . ' is not set as an environment variable and no default value is set'
            );
        }

        return $default;
    }
}
