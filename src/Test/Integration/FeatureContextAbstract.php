<?php
declare(strict_types = 1);

namespace ShoppinPal\YapepCommon\Test\Integration;


use Behat\Behat\Context\Context;
use ShoppinPal\YapepCommon\Bootstrap\BootstrapAbstract;
use ShoppinPal\YapepCommon\Storage\StorageFactory;
use YapepBase\Config;
use YapepBase\Exception\Exception;
use YapepBase\File\FileHandlerPhp;
use YapepBase\Request\HttpRequest;


/**
 * Class FeatureContextAbstract
 *
 * @package Test\Integration
 */
abstract class FeatureContextAbstract implements Context
{

    /**
     * @var string
     */
    protected $responseBody;

    /**
     * @var string
     */
    protected $responseHeaders;

    /**
     * @var array
     */
    protected $sentParams = array();

    /**
     * @var string
     */
    protected $calledUrl;

    /**
     * @return DbInitializerAbstract
     */
    abstract protected function getDbInitializer();

    /**
     * @return array
     */
    abstract protected function getLogResourceNames();

    /**
     * @return array
     */
    abstract protected function getStorageNames();

    /**
     * @BeforeScenario
     */
    public function initScenario()
    {
        $this->getDbInitializer()->initDatabase();
        $this->initLogs();
        $this->cleanUpStorages();
    }

    /**
     * @AfterScenario
     * @throws \YapepBase\Exception\Exception
     */
    public function afterScenario()
    {
        $loggedErrors = $this->getLoggedErrors();

        if (!empty($loggedErrors)) {
            throw new Exception('Errors logged during the test: ' . PHP_EOL . implode(PHP_EOL, $loggedErrors));
        }
    }

    public function initLogs()
    {
        $config = Config::getInstance();
        foreach ($this->getLogResourceNames() as $resourceName) {
            $errorLogPath = $config->get('resource.log.' . $resourceName . '.path');

            $fileHandler = new FileHandlerPhp();

            if ($fileHandler->checkIsPathExists($errorLogPath)) {
                $fileHandler->remove($errorLogPath);
            }
        }
    }

    /**
     * Cleans the given storage.
     *
     * @return void
     */
    protected function cleanUpStorages()
    {
        foreach ($this->getStorageNames() as $storageName) {
            StorageFactory::get($storageName)->clear();
        }
    }


    protected function getLoggedErrors(): array
    {
        $errorLogPath = Config::getInstance()->get('resource.log.error.path');

        $fileHandlerPhp = new FileHandlerPhp();

        if (!$fileHandlerPhp->checkIsPathExists($errorLogPath)) {
            return [];
        }

        $errorString = $fileHandlerPhp->getAsString($errorLogPath, 0);

        if (empty($errorString)) {
            return [];
        }
        $errors = [];
        foreach (explode(PHP_EOL, $errorString) as $error) {
            $errors[] = json_decode($error, true)['message'];
        }
        return $errors;
    }

    protected function callUri(string $url, string $method = HttpRequest::METHOD_HTTP_GET, array $params = [])
    {
        $getParams = [BootstrapAbstract::GET_PARAM_NAME_TESTING_MODE => 1];

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ];
        $this->sentParams = $params;

        switch ($method) {
            case HttpRequest::METHOD_HTTP_GET:
                $options[CURLOPT_HTTPGET] = true;
                $getParams = array_merge($params, $getParams);
                break;

            case HttpRequest::METHOD_HTTP_POST:
                $options[CURLOPT_POST] = true;
                $formattedParameters = array();
                $this->formatDataForPost($params, $formattedParameters);
                $options[CURLOPT_POSTFIELDS] = $formattedParameters;
                break;

            default:
                throw new \Exception('Invalid method given: ' . $method);
                break;

        }

        $url = $url
            . (strpos($url, '?') === false ? '?' : '&')
            . http_build_query($getParams);
        $curl = curl_init($url);
        curl_setopt_array($curl, $options);
        $result = curl_exec($curl);

        if (false === $result) {
            $this->error = curl_error($curl);

            throw new \Exception('Curl Error:' . curl_error($curl));
        }
        $this->calledUrl = $url;

        $info = curl_getinfo($curl);
        curl_close($curl);

        $this->responseBody = (string)substr($result, $info['header_size']);
        $this->responseHeaders = (string)substr($result, 0, $info['header_size']);
    }


    protected function getResponseBody(): array
    {
        return json_decode($this->responseBody, true);
    }

    protected function getResponseHeaders(): string
    {
        return $this->responseHeaders;
    }


    protected function formatDataForPost(array $post, array &$output, string $paramNamePrefix = null)
    {
        foreach ($post as $key => $value) {
            $currentKey = !empty($paramNamePrefix) ? $paramNamePrefix . '[' . $key . ']' : $key;

            if (is_array($value) || is_object($value)) {
                $this->formatDataForPost($value, $output, $currentKey);
            }
            else {
                $output[$currentKey] = $value;
            }
        }
    }


    protected function getLoggedEntries(string $logResourceName): array
    {
        $logPath = Config::getInstance()->get('resource.log.' . $logResourceName . '.path');
        $logEntries = (new FileHandlerPhp())->getAsString($logPath);

        if (empty($logEntries)) {
            return array();
        }

        $loggedEntries = array();
        foreach (explode(PHP_EOL, trim($logEntries, PHP_EOL)) as $logEntry) {
            $loggedEntries[] = json_decode($logEntry, true);
        }
        return $loggedEntries;
    }

    /**
     * @Then the http status of the response should be :statusCode
     */
    public function theHttpStatusOfTheResponseShouldBe(int $statusCode)
    {
        \PHPUnit_Framework_Assert::assertContains(
            'HTTP/1.1 ' . $statusCode . ' ', $this->responseHeaders, 'Expected status code not received'
        );
    }
}
