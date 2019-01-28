<?php
declare(strict_types=1);

namespace ShoppinPal\YapepCommon\HealthCheck;

use ShoppinPal\YapepCommon\Exception\HealthCheckException;
use YapepBase\Application;
use YapepBase\Communication\CurlHttpRequest;
use YapepBase\Communication\CurlHttpRequestResult;
use YapepBase\Config;
use YapepBase\Exception\ParameterException;

class HttpHealthCheck implements IHealthCheck
{
    public function checkServiceHealth(array $configOptions): void
    {
        $url          = $this->getUrl($configOptions);
        $result       = $this->sendRequest($configOptions, $url);
        $error = $result->getError();

        if (!empty($error)) {
            throw new HealthCheckException('Curl request failed with error: ' . $error);
        }

        $responseBody = $result->getResponseBody();

        $this->checkStatus($configOptions, $result, $url);
        $this->checkJson($configOptions, $responseBody);
        $this->checkBody($configOptions, $responseBody);
    }

    protected function checkArrayContains(array $expected, array $array): bool
    {
        foreach ($expected as $key => $value) {
            if (!isset($array[$key])) {
                return false;
            }

            if (is_array($value)) {
                if (!is_array($array[$key]) || !$this->checkArrayContains($value, $array[$key])) {
                    return false;
                }
            } elseif ($array[$key] != $value) {
                return false;
            }
        }

        return true;
    }

    protected function getUrl(array $configOptions): string
    {
        if (isset($configOptions['url'])) {
            $url = $configOptions['url'];
        } elseif (isset($configOptions['urlConfigName'])) {
            $url = Config::getInstance()->get($configOptions['urlConfigName']);
        } else {
            throw new ParameterException('Either url or urlConfigName must be set');
        }

        if (!strstr($url, '://')) {
            $url = ($configOptions['scheme'] ?? 'http://') . $url;
        }

        if (!empty($configOptions['path'])) {
            $url .= '/' . ltrim($configOptions['path'], '/');
        }

        return $url;
    }

    protected function sendRequest(array $configOptions, string $url): CurlHttpRequestResult
    {
        $request = Application::getInstance()
            ->getDiContainer()
            ->getCurlHttpRequest();

        $request->setUrl($url);
        $request->setMethod($configOptions['method'] ?? CurlHttpRequest::METHOD_GET);

        if (!empty($configOptions['headers'])) {
            $request->setHeaders($configOptions['headers']);
        }

        return $request->send();
    }

    protected function checkStatus(array $configOptions, CurlHttpRequestResult $result, string $url): void
    {
        if (!empty($result->getError())) {
            throw new HealthCheckException('Failed to send request to ' . $url . '. Error: ' . $result->getError());
        }

        $expectedCode = $configOptions['expectedCode'] ?? 200;

        if ($result->getResponseCode() != $expectedCode) {
            throw new HealthCheckException(
                'Expected a status code of ' . $expectedCode . ', got ' . $result->getResponseCode()
            );
        }
    }

    protected function checkJson(array $configOptions, $responseBody): void
    {
        if (!empty($configOptions['expectedJson'])) {
            if (!is_array($configOptions['expectedJson'])) {
                throw new ParameterException('expectedJson must be an array');
            }

            $resultJson = json_decode($responseBody, true);

            if (false === $resultJson && json_last_error()) {
                throw new HealthCheckException(
                    'Expected a JSON response, but the response failed to decode as a JSON. Error : ' . json_last_error(
                    ) . '. Response: ' . $responseBody
                );
            }

            if (!$this->checkArrayContains($configOptions['expectedJson'], $resultJson)) {
                throw new HealthCheckException(
                    'The response does not contain the expected JSON content: ' . json_encode(
                        $configOptions['expectedJson'],
                        JSON_PRETTY_PRINT
                    ) . '. Response: ' . json_encode($resultJson, JSON_PRETTY_PRINT)
                );
            }
        }
    }

    protected function checkBody(array $configOptions, $responseBody): void
    {
        if (isset($configOptions['expectedResponse'])) {
            if (trim($configOptions['expectedResponse']) != trim($responseBody)) {
                throw new HealthCheckException(
                    'Invalid response. Expected: ' . $configOptions['expectedResponse'] . '. Received: ' . $responseBody
                );
            }
        }
    }

}
