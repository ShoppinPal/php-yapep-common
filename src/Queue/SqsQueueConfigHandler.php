<?php

namespace ShoppinPal\YapepCommon\Queue;

use ShoppinPal\YapepCommon\Helper\DependencyInjectionHelper;
use YapepBase\Config;

/**
 * Helper class for handling the SQS queue configurations
 */
class SqsQueueConfigHandler
{

    /**
     * @var Config
     */
    protected $config;

    /**
     * SqsQueueConfigHandler constructor.
     */
    public function __construct()
    {
        $this->config = Config::getInstance();
    }

    /**
     * Returns the SQS name of the queue identified by it's config resource name.
     *
     * @param string $configName
     *
     * @return string
     */
    public function getNameForQueue($configName)
    {
        return $this->getNamePrefix()
            . $this->config->get($this->getResourceConfigKey($configName, 'name'))
            . $this->getNameSuffixByEnvironment();
    }

    /**
     * Returns the serializer type for the queue identified by it's config resource name.
     *
     * @param string $configName
     * @param string $default
     *
     * @return string The type of the serializer (@uses SQS::SERIALIZER_*), or the fully qualified classname
     *                of the serializer class to use.
     */
    public function getSerializerForQueue($configName, $default)
    {
        return $this->config->get($this->getResourceConfigKey($configName, 'serializer'), $default);
    }

    /**
     * Returns the attributes for to be used for the specified queue resource, when a new queue is created in SQS
     *
     * @param string $configName
     *
     * @return SqsQueueConfigDo
     */
    public function getNewQueueAttributes($configName)
    {
        $isProduction = !in_array(DependencyInjectionHelper::getEnvironment(), [ENVIRONMENT_DEV, ENVIRONMENT_TEST]);

        $redriveDeadLetterTargetArn = $this->getConfig($configName, 'redriveDeadLetterTargetArn');

        if ($redriveDeadLetterTargetArn) {
            // IF the dead letter queue is specified, replace the prefix and suffix templates
            $redriveDeadLetterTargetArn = str_replace(
                '{PREFIX}',
                $this->getNamePrefix(),
                str_replace('{SUFFIX}', $this->getNameSuffixByEnvironment(), $redriveDeadLetterTargetArn)
            );
        }

        return new SqsQueueConfigDo(
            $this->getConfig($configName, 'delaySeconds', 0),
            $this->getConfig($configName, 'maximumMessageSize', 262144),
            $this->getConfig($configName, 'messageRetentionPeriodSeconds', $isProduction ? 1209600 : 345600),
            $this->getConfig($configName, 'receiveMessageWaitTimeSeconds', 0),
            $this->getConfig($configName, 'redriveMaxReceiveCount'),
            $redriveDeadLetterTargetArn,
            $this->getConfig($configName, 'visibilityTimeoutSeconds', 30)
        );
    }

    /**
     * Returns the config key for the specified resource.
     *
     * @param string $configName The name of the queue config resource
     * @param string $subKey     The subkey to return.
     *
     * @return string
     */
    protected function getResourceConfigKey($configName, $subKey)
    {
        return 'commonResource.sqsQueue.' . $configName . '.' . $subKey;
    }

    /**
     * Returns the configuration value for the specified config resource and subkey.
     *
     * It will first try to find the configuration value for the queue resource, if that's not specified, it will
     * check the default in common.sqsAttributes, if that's not set either, it will use the provided default.
     *
     * @param string $configName   The name of the queue resource config.
     * @param string $subKey       The subkey for the config.
     * @param mixed  $defaultValue The default value to return, if neither the queue resource, nor the common
     *                             default is set in the config.
     *
     * @return array|mixed
     */
    protected function getConfig($configName, $subKey, $defaultValue = false)
    {
        return $this->config->get(
            $this->getResourceConfigKey($configName, $subKey),
            $this->config->get('common.sqsQueueDefault.' . $subKey, $defaultValue)
        );
    }

    /**
     * Returns the prefix to add to all queue names in the current project.
     *
     * @return string
     */
    protected function getNamePrefix()
    {
        return $this->config->get('system.project.name') . '-';
    }

    /**
     * Returns the suffix to add to all queue names in the current environment.
     *
     * @return string
     */
    protected function getNameSuffixByEnvironment()
    {
        switch (DependencyInjectionHelper::getEnvironment()) {
            case ENVIRONMENT_DEV:
                $suffix = '-dev';
                if (defined('IS_INNER_TESTING') && IS_INNER_TESTING) {
                    $suffix .= '-automated-test';
                }
                return $suffix;

            case ENVIRONMENT_TEST:
                return '-test';

            default:
                return '';
        }
    }
}
