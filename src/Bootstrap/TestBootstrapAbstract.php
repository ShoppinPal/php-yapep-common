<?php
declare(strict_types = 1);

namespace ShoppinPal\YapepCommon\Bootstrap;


use YapepBase\Application;
use YapepBase\Autoloader\SimpleAutoloader;
use YapepBase\ErrorHandler\EchoErrorHandler;
use YapepBase\ErrorHandler\ExceptionCreatorErrorHandler;
use YapepBase\File\FileHandlerPhp;

abstract class TestBootstrapAbstract extends BootstrapAbstract
{

    /**
     * @var SimpleAutoloader
     */
    protected $autoloader;

    /**
     * Does the bootstrap for test usage
     *
     * @param string $rootDir
     * @param string $vendorDir
     * @param string $baseClassDir
     */
    protected function testBootstrap(string $rootDir, string $vendorDir, string $baseClassDir)
    {
        $this->initEnvironment();
        $this->defineEnvironmentConstants();
        $this->verifyEnvironment();

        $this->setupSimpleAutoloader($vendorDir, $baseClassDir, $rootDir . '/test');
        $this->initApplicationsAutoloading($rootDir);

        $this->setupEnvironmentInDi();

        $this->loadConfig($rootDir);

        $errorHandlerRegistry = Application::getInstance()->getDiContainer()->getErrorHandlerRegistry();
        $errorHandlerRegistry->addErrorHandler(new ExceptionCreatorErrorHandler(E_ALL | E_STRICT));
        $errorHandlerRegistry->addErrorHandler(new EchoErrorHandler());
        $errorHandlerRegistry->register();
    }

    protected function verifyEnvironment()
    {
        parent::verifyEnvironment();

        if (!defined('IS_INNER_TESTING')) {
            /** Indicates if the current run is an inner test. */
            define('IS_INNER_TESTING', true);
        }
    }

    /**
     * @param string $logConfigName
     * @param string $fileStorageConfigName
     */
    protected function setupErrorHandling($logConfigName = 'error', $fileStorageConfigName = 'error')
    {
        Application::getInstance()->getDiContainer()->getErrorHandlerRegistry()->addErrorHandler(
            new ExceptionCreatorErrorHandler()
        );
    }

    /**
     * @param string $rootDir
     */
    protected function initApplicationsAutoloading(string $rootDir)
    {
        $fileHandler = new FileHandlerPhp();

        $applicationDirs = $fileHandler->getListByGlob($rootDir, 'app/*/class');
        $batchDirs = $fileHandler->getListByGlob($rootDir, 'batch/*/class');

        // We prefix the returned paths with the given rootDir to make them absolute
        foreach ($applicationDirs as $index => $applicationDir) {
            $applicationDirs[$index] = $rootDir . DIRECTORY_SEPARATOR . $applicationDir;
        }
        foreach ($batchDirs as $index => $batchDir) {
            $batchDirs[$index] = $rootDir . DIRECTORY_SEPARATOR . $batchDir;
        }

        $applicationDirs = empty($applicationDirs) ? array() : $applicationDirs;
        $batchDirs = empty($batchDirs) ? array() : $batchDirs;

        $autoloadDirs = array_merge($applicationDirs, $batchDirs);
        foreach ($autoloadDirs as $autoloadDir) {
            $this->autoloader->addClassPath($autoloadDir);
        }
    }
}
