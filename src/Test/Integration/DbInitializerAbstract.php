<?php
declare(strict_types = 1);

namespace ShoppinPal\YapepCommon\Test\Integration;


use YapepBase\Config;
use YapepBase\Exception\Exception;

/**
 * Responsible for initializing the Database for integration testing.
 */
abstract class DbInitializerAbstract
{

    /**
     * @return string
     */
    abstract protected function getResourceName();

    /**
     * @return string
     */
    abstract protected function getSchemaFilePath();


    public function initDataBase()
    {
        $dbConfig = Config::getInstance()->get('resource.database.' . $this->getResourceName() . '.rw.*');

        $testDatabase = $dbConfig['database'];

        $dropDatabaseSql = 'DROP DATABASE IF EXISTS ' . $testDatabase . '; CREATE DATABASE ' . $testDatabase . ';';

        $this->executeSql($dbConfig, '-e ' . escapeshellarg($dropDatabaseSql));
        $this->executeSql($dbConfig, $testDatabase . ' < ' . escapeshellarg($this->getSchemaFilePath()));
    }

    /**
     * @param array  $dbConfig
     * @param string $commandPart
     *
     * @throws \YapepBase\Exception\Exception
     */
    protected function executeSql(array $dbConfig, string $commandPart)
    {
        $charset      = empty($dbConfig['charset']) ? 'utf8' : $dbConfig['charset'];
        $testHostname = $dbConfig['host'];
        $testUser     = $dbConfig['user'];
        $testPassword = $dbConfig['password'];

        $fullSqlCommand = 'mysql'
            . ' -h ' . $testHostname
            . ' -u ' . $testUser
            . ' --default-character-set ' . $charset
            . ' -p' . escapeshellarg($testPassword)
            . ' ' . $commandPart
            . ' 2>&1';

        $output = array();
        $exitCode = 0;
        exec($fullSqlCommand, $output, $exitCode);

        foreach ($output as $index => $line) {
            if ($line == 'mysql: [Warning] Using a password on the command line interface can be insecure.') {
                unset($output[$index]);
            }
        }

        if ($exitCode != 0 && !empty($output)) {
            throw new Exception('Error while initializing test DB. Error message: ' . implode('\n', $output));
        }
    }
}
