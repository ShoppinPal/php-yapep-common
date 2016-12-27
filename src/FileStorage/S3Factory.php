<?php

namespace ShoppinPal\YapepCommon\FileStorage;

use YapepBase\Exception\ConfigException;

class S3Factory
{

    /**
     * @var array
     */
    protected static $clients = [];

    /**
     * Returns an S3 instance by the specified config name.
     *
     * @param string $configName
     *
     * @return S3
     *
     * @throws ConfigException If the specified S3 instance is not configured properly.
     */
    public static function getClient($configName)
    {
        if (!isset(static::$clients[$configName])) {
            static::$clients[$configName] = new S3($configName);
        }

        return static::$clients[$configName];
    }

    /**
     * Sets a client instance for the specified config name.
     *
     * Should only be used while testing!
     *
     * @param string $configName
     * @param S3     $client
     *
     * @return void
     */
    public static function setClient($configName, S3 $client)
    {
        static::$clients[$configName] = $client;
    }
}
