<?php

namespace ShoppinPal\YapepCommon\Email;

use YapepBase\Config;
use YapepBase\Exception\ConfigException;
use YapepBase\Exception\ParameterException;

class EmailFactory
{
    const TYPE_SES = 'ses';

    const TYPE_SWIFTMAIL = 'swiftmail';

    /**
     * @var array
     */
    protected static $mailers = [];

    /**
     * Returns an SES instance by the specified config name.
     *
     * @param string $configName
     *
     * @return IEmail
     *
     * @throws ConfigException If the specified e-mail instance is not configured properly.
     * @throws ParameterException If the emailer type is invalid.
     */
    public static function getClient($configName)
    {
        if (isset(static::$mailers[$configName])) {
            return static::$mailers[$configName];
        }

        $config = Config::getInstance();

        $type = $config->get('commonResource.email.' . $configName . '.type');

        switch ($type) {
            case self::TYPE_SES:
                $queue = new Ses($configName);
                break;

            case self::TYPE_SWIFTMAIL:
                $queue = new SwiftMail($configName);
                break;

            default:
                throw new ParameterException('Invalid email type: ' . $type);
        }

        static::$mailers[$configName] = $queue;

        return $queue;
    }

    /**
     * Sets an email instance for the specified config name.
     *
     * Should only be used while testing!
     *
     * @param string $configName
     * @param IEmail $email
     *
     * @return void
     */
    public static function setClient($configName, IEmail $email)
    {
        static::$mailers[$configName] = $email;
    }

}
