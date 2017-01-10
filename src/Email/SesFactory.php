<?php

namespace ShoppinPal\YapepCommon\Email;

use YapepBase\Exception\ConfigException;

class SesFactory
{
    /**
     * @var array
     */
    protected static $clients = [];

    /**
     * Returns an SES instance by the specified config name.
     *
     * @param string $configName
     *
     * @return Ses
     *
     * @throws ConfigException If the specified SES instance is not configured properly.
     */
    public static function getClient($configName)
    {
        if (!isset(static::$clients[$configName])) {
            static::$clients[$configName] = new Ses($configName);
        }

        return static::$clients[$configName];
    }

    /**
     * Sets a client instance for the specified config name.
     *
     * Should only be used while testing!
     *
     * @param string $configName
     * @param Ses     $client
     *
     * @return void
     */
    public static function setClient($configName, Ses $client)
    {
        static::$clients[$configName] = $client;
    }
}
