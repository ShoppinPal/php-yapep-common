<?php
namespace ShoppinPal\YapepCommon\Robo;

use josegonzalez\Dotenv\Loader;
use Robo\Tasks;

abstract class RoboFileAbstract extends Tasks
{
    /**
     * Prepares the execution environment (private_envconfig.php and private_environment.conf)
     *
     * @option $environment The current environment to use
     */
    public function buildEnvironment($opts = ['environment|e' => null])
    {
        $envPath = $this->getEnvFilePath();

        if (file_exists($envPath)) {
            return;
        }

        $validEnvironments = [
            'dev',
            'test',
            'staging',
            'production'
        ];

        if (!empty($opts['environment']) && in_array($opts['environment'], $validEnvironments)) {
            $environment = $opts['environment'];
        } else {
            do {
                $environment = $this->askDefault(
                    'Please select the environment (' . implode(', ', $validEnvironments) . ')',
                    'dev'
                );
            } while (!in_array($environment, $validEnvironments));
        }

        $envFileContent = '';

        foreach (file($this->getEnvExamplePath()) as $line) {
            if (strstr($line, 'ENVIRONMENT_NAME')) {
                $envFileContent .= 'ENVIRONMENT_NAME=' . $environment . "\n";
            } else {
                $envFileContent .= $line;
            }
        }

        file_put_contents($this->getEnvFilePath(), $envFileContent);

        $this->say('Set current environment to ' . $environment . ' and created .env file.');
        $this->say('Do not forget to fix the credentials in it!');
    }

    /**
     * Install composer dependencies
     *
     * @option $no-dev Do not install development dependencies
     */
    public function buildComposer($opts = ['no-dev' => false])
    {
        $composer = $this->taskComposerInstall();

        if (!$this->isRequiredPhpVersion()) {
            $composer->option('ignore-platform-reqs');
        }

        if ($opts['no-dev']) {
            $composer->noDev();
        }

        return $composer->run();
    }

    /**
     * Run the migrations
     */
    public function migrate()
    {
        $this->requireMinimumRequirements();

        return $this->taskExec('vendor/bin/phinx')->option('ansi')->arg('migrate')->run();
    }

    /**
     * Run the behat tests
     *
     * @option $silent Produces less output
     */
    public function testBehat($opts = ['silent|s' => false])
    {
        $this->requireMinimumRequirements();

        return $this->taskBehat()->format($opts['silent'] ? 'progress' : 'pretty')->colors()->run();
    }

    /**
     * Generates the API documentation
     */
    public function generateApiDoc()
    {
        foreach ($this->getApplicationBasePaths() as $basePath) {
            $this->taskExec('vendor/bin/swagger')
                ->arg($basePath)
                ->option('--output')
                ->arg($basePath . '/www/swagger.json')
                ->run();
        }
    }

    /**
     * Updates the .env file from the .env.example file.
     *
     * @param bool   $doUpdate
     *
     * @return void
     */
    protected function updateEnvFile($doUpdate = false)
    {
        $envPath     = $this->getEnvFilePath();
        $examplePath = $this->getEnvExamplePath();
        $env         = (new Loader([$envPath]))->parse()->toArray();
        $example     = (new Loader([$examplePath]))->parse()->toArray();
        $additions   = [];

        foreach ($example as $key => $value) {
            if (!empty($value) && empty($env[$key])) {
                $additions[$key] = $value;
            }
        }

        if (!empty($additions)) {
            $content = file_get_contents($envPath);

            foreach ($additions as $key => $value) {
                $content = preg_replace('/^\s*#*\s*(' . preg_quote($key, '/') . '\s*=[^\n]*$)/m', '#$1', $content);
                $content .= "\n$key=$value\n";
            }

            if ($doUpdate && 'dev' === ($env['ENVIRONMENT_NAME'] ?? null)) {
                file_put_contents($envPath, $content);
            } else {
                $this->say('Your .env file is outdated, please update it with the following content:');
                $this->io()->block($content);
            }
        }
    }

    /**
     * Checks if we can run in the current execution environment
     *
     * @throws Exception
     */
    protected function requireMinimumRequirements()
    {
        if (!$this->isRequiredPhpVersion() || !$this->isCurlInstalled() || !$this->isMemcachedInstalled()) {
            throw new Exception(
                'Your host does not match the minimum requirements, please run Robo inside the container'
                . ' via "docker-compose exec php-web vendor/bin/robo"'
            );
        }
    }

    /*
     * Checks whether the minimum PHP version is installed
     *
     * return bool
     */
    protected function isRequiredPhpVersion()
    {
        return PHP_MAJOR_VERSION > 7 || (PHP_MAJOR_VERSION == 7 && PHP_MINOR_VERSION >= 1);
    }

    /**
     * Checks whether the curl extension is installed
     *
     * @return bool
     */
    protected function isCurlInstalled()
    {
        return function_exists('curl_exec');
    }

    /**
     * Checks whether the memcached extension is installed
     *
     * @return bool
     */
    protected function isMemcachedInstalled()
    {
        return class_exists('\Memcached');
    }

    /**
     * Returns the base paths for all applications in the project
     *
     * @return array
     */
    abstract protected function getApplicationBasePaths();

    /**
     * Returns the full path to the .env file
     *
     * @return string
     */
    abstract protected function getEnvFilePath();

    /**
     * Returns the full path to the .env.example file
     *
     * @return string
     */
    abstract protected function getEnvExamplePath();
}
