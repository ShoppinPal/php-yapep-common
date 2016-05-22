<?php

namespace ShoppinPal\YapepCommon\Helper;

use YapepBase\Config;

class ConfigHelper
{

    /**
     * Temporary helper method for creating database config from an URL in an environment variable.
     *
     * @param string $variableName   Name of the environment variable to parse.
     * @param string $connectionName Name of the connection to create.
     *
     * @return void
     *
     * @todo this in a nicer way in yapep 1.0
     */
    public static function setupMySqlDbConfig($variableName, $connectionName)
    {
        $parsed = parse_url(getenv($variableName));

        $config = Config::getInstance();

        $config->set(
            [
                'resource.database.' . $connectionName . '.rw.backendType' => 'mysql',
                'resource.database.' . $connectionName . '.rw.host'        => $parsed['host'],
                'resource.database.' . $connectionName . '.rw.user'        => $parsed['user'],
                'resource.database.' . $connectionName . '.rw.password'    => $parsed['pass'],
                'resource.database.' . $connectionName . '.rw.database'    => ltrim($parsed['path'], '/'),
                'resource.database.' . $connectionName . '.rw.port'        => $parsed['port'],
                'resource.database.' . $connectionName . '.rw.charset'     => 'utf8',

                'resource.database.' . $connectionName . '.ro.backendType' => 'mysql',
                'resource.database.' . $connectionName . '.ro.host'        => $parsed['host'],
                'resource.database.' . $connectionName . '.ro.user'        => $parsed['user'],
                'resource.database.' . $connectionName . '.ro.password'    => $parsed['pass'],
                'resource.database.' . $connectionName . '.ro.database'    => ltrim($parsed['path'], '/'),
                'resource.database.' . $connectionName . '.ro.port'        => $parsed['port'],
                'resource.database.' . $connectionName . '.ro.charset'     => 'utf8',
            ]
        );
    }


}
