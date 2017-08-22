<?php
declare(strict_types = 1);

namespace ShoppinPal\YapepCommon\Test\Integration;


use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use ShoppinPal\YapepCommon\Bootstrap\BootstrapAbstract;
use ShoppinPal\YapepCommon\Storage\StorageFactory;
use YapepBase\Communication\CurlHttpRequest;
use YapepBase\Communication\CurlHttpRequestResult;
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

    protected function callUrl(string $url, string $method = CurlHttpRequest::METHOD_GET, array $params = [], $storeResponse = true): CurlHttpRequestResult
    {
        $params[BootstrapAbstract::GET_PARAM_NAME_TESTING_MODE] = 1;
        $this->sentParams = $params;
        $this->calledUrl = $url;

        $request = new CurlHttpRequest();
        $request->setUrl($url);
        $request->setMethod($method);
        $request->setPayload($params, CurlHttpRequest::PAYLOAD_TYPE_QUERY_STRING_ARRAY);

        $result = $request->send();
        if ($storeResponse) {
            $this->responseHeaders = $result->getResponseHeaders();
            $this->responseBody = $result->getResponseBody();
        }

        return $result;
    }

    protected function callRestApi(
        string $url,
        string $method = CurlHttpRequest::METHOD_GET,
        array $params = [],
        array $headers = [],
        $storeResponse = true
    ): CurlHttpRequestResult
    {
        $url = $url
            . (strpos($url, '?') === false ? '?' : '&')
            . BootstrapAbstract::GET_PARAM_NAME_TESTING_MODE . '=1';

        $request = new CurlHttpRequest();
        $request->setUrl($url);
        $request->setMethod($method);
        $request->addHeader('Content-Type: application/json');
        if (!empty($this->sessionId)) {
            $request->addHeader('Authorization: Session ' . $this->sessionId);
        }
        foreach ($headers as $header) {
            $request->addHeader($header);
        }

        $request->setPayload(json_encode($params), CurlHttpRequest::PAYLOAD_TYPE_RAW);

        $result = $request->send();
        if ($storeResponse) {
            $this->responseHeaders = $result->getResponseHeaders();
            $this->responseBody = $result->getResponseBody();
        }

        return $result;
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
        Assert::assertContains(
            'HTTP/1.1 ' . $statusCode . ' ', $this->responseHeaders, 'Expected status code not received'
        );
    }
}
