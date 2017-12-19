<?php
declare(strict_types = 1);

namespace ShoppinPal\YapepCommon\Test\Integration;


use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use ShoppinPal\YapepCommon\Storage\StorageFactory;
use YapepBase\Application;
use YapepBase\Exception\Exception;


/**
 * Class FeatureContextAbstract
 *
 * @package Test\Integration
 */
abstract class FeatureContextAbstract implements Context
{

    /**
     * @var ErrorLogHandler
     */
    protected $errorLogHandler;

    /**
     * @var LogHandler
     */
    protected $logHandler;

    /**
     * @var RequestHandler
     */
    protected $requestHandler;

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
     * @see initScenario()
     */
    protected function doAfterInitScenario()
    {
    }

    /**
     * @BeforeScenario
     */
    public function initScenario()
    {
        $this->errorLogHandler = new ErrorLogHandler();
        $this->logHandler      = new LogHandler();
        $this->requestHandler  = new RequestHandler();

        $this->getDbInitializer()->initDatabase();
        $this->logHandler->initLogs($this->getLogResourceNames());
        $this->cleanUpStorages();
        Application::getInstance()->getDiContainer()->getViewDo()->clear();
        $this->doAfterInitScenario();
    }

    /**
     * @AfterScenario
     * @throws \YapepBase\Exception\Exception
     */
    public function afterScenario()
    {
        $loggedErrors = $this->errorLogHandler->getLoggedErrors();

        if (!empty($loggedErrors)) {
            throw new Exception('Errors logged during the test: ' . PHP_EOL . implode(PHP_EOL, $loggedErrors));
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

    /**
     * @Then the http status of the response should be :statusCode
     */
    public function theHttpStatusOfTheResponseShouldBe(int $statusCode)
    {
        Assert::assertContains(
            'HTTP/1.1 ' . $statusCode . ' ', $this->requestHandler->getResponseHeaders(), 'Expected status code not received'
        );
    }
}
