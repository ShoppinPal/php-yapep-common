<?php
namespace ShoppinPal\YapepCommon;

use YapepBase\Exception\Exception;
use YapepBase\Request\HttpRequest;
use YapepBase\Response\HttpResponse;
use YapepBase\Response\PhpOutput;

/**
 * Abstract class, that should be used as the base for application resource managers.
 */
abstract class ApplicationResourceManagerAbstract
{

    /**
     * Returns the request instance for the specified application.
     *
     * @param string $applicationName The application's name. {@uses self::APPLICATION_*}
     *
     * @return \YapepBase\Request\HttpRequest
     *
     * @throws \YapepBase\Exception\Exception   If the application name is invalid.
     */
    public function getRequest($applicationName)
    {
        if (!$this->checkApplicationName($applicationName)) {
            throw new Exception('Invalid application name: ' . $applicationName);
        }

        return $this->getHttpRequest();
    }

    /**
     * Returns the response instance for the specified application.
     *
     * @param string $applicationName The application's name. {@uses self::APPLICATION_*}
     *
     * @return \YapepBase\Response\HttpResponse
     *
     * @throws \YapepBase\Exception\Exception   If the application name is invalid.
     */
    public function getResponse($applicationName)
    {
        if (!$this->checkApplicationName($applicationName)) {
            throw new Exception('Invalid application name: ' . $applicationName);
        }

        return $this->getHttpResponse();
    }

    /**
     * Returns a HttpRequest instance with the default configuration.
     *
     * @return \YapepBase\Request\HttpRequest
     */
    protected function getHttpRequest()
    {
        return new HttpRequest($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES);
    }

    /**
     * Returns a HttpResponse instance with the default configuration.
     *
     * @return \YapepBase\Response\HttpResponse
     */
    protected function getHttpResponse()
    {
        return new HttpResponse(new PhpOutput());
    }

    /**
     * Returns TRUE if the specified application name is valid, FALSE otherwise.
     *
     * @param string $applicationName The application's name. {@uses self::APPLICATION_*}
     *
     * @return bool
     */
    abstract protected function checkApplicationName($applicationName);

    /**
     * Returns the router for the specified application.
     *
     * @param string                         $applicationName The application's name. {@uses self::APPLICATION_*}
     * @param \YapepBase\Request\HttpRequest $request         The request for the application.
     *
     * @return \YapepBase\Router\IRouter
     *
     * @throws \YapepBase\Exception\Exception   If the application name is invalid.
     */
    abstract public function getRouter($applicationName, HttpRequest $request);

    /**
     * Returns the reverse router for the specified application.
     *
     * @param string $applicationName The application's name. {@uses self::APPLICATION_*}
     * @param string $language        The requested language for the reverse router.
     *
     * @return \YapepBase\Router\IReverseRouter
     *
     */
    abstract public function getReverseRouter($applicationName, $language = null);
}
