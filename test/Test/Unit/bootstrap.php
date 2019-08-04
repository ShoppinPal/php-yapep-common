<?php
/**
 * Bootstrap for unit tests.
 */

namespace Test\Unit;


use ShoppinPal\YapepCommon\Bootstrap\TestBootstrapAbstract;

/** The repo root directory */
define('ROOT_DIR', realpath(__DIR__ . '/../../../'));

/** The base class directory */
define('CLASS_DIR', realpath(ROOT_DIR . '/src/'));

require CLASS_DIR . '/Bootstrap/TestBootstrapAbstract.php';

/**
 * Bootstrap class
 *
 * @package Redirector
 */
class Bootstrap extends TestBootstrapAbstract
{

    /**
     * Starts the bootstrap process.
     *
     * @return void
     */
    public function start(): void
    {
        $this->testBootstrap(ROOT_DIR, ROOT_DIR . '/vendor/', CLASS_DIR);
    }

    protected function loadConfig($rootDir): void
    {
    }

    protected function verifyEnvironment(): void
    {
        define('ENVIRONMENT', ENVIRONMENT_DEV);
    }

}

$bootstrap = new Bootstrap();
$bootstrap->start();
