<?php

namespace ShoppinPal\YapepCommon\Helper;

use YapepBase\Application;

/**
 * Helper class for the DI container
 */
class DependencyInjectionHelper
{

    /** Key to the current running environment {@uses ENVIRONMENT_*} */
    const KEY_ENVIRONMENT = 'environment';

    /**
     * Returns the current running environment.
     *
     * @return string
     *
     * @throws \YapepBase\Exception\ParameterException   If the environment is not set.
     */
    public static function getEnvironment()
    {
        return Application::getInstance()->getDiContainer()->offsetGet(self::KEY_ENVIRONMENT);
    }

    /**
     * Returns whether the environment is set.
     *
     * @return bool
     */
    public static function hasEnvironment()
    {
        return Application::getInstance()->getDiContainer()->offsetExists(self::KEY_ENVIRONMENT);
    }
}
