<?php
namespace ShoppinPal\YapepCommon\HealthCheck\Batch;

use Carbon\CarbonImmutable;
use YapepBase\Batch\BatchScript;
use YapepBase\Exception\Batch\AbortException;
use YapepBase\Exception\Exception;

class LastRunChecker extends BatchScript
{
    protected const USAGE_NORMAL = 'normal';

    /** @var string */
    protected $path;
    /** @var int */
    protected $thresholdSeconds;

    protected function prepareSwitches()
    {
        parent::prepareSwitches();

        $this->usageIndexes[self::USAGE_NORMAL] = $this->cliHelper->addUsage('Normal usage');

        $this->cliHelper->addSwitch(
            'p',
            'path',
            'The path to the file containing the timestamp of the last health check',
            $this->usageIndexes[self::USAGE_NORMAL],
            false,
            'path'
        );
        $this->cliHelper->addSwitch(
            't',
            'threshold-seconds',
            'The number of seconds to allow to pass since the timestamp in the file to treat the check as healthy',
            $this->usageIndexes[self::USAGE_NORMAL],
            false,
            'seconds'
        );
    }

    protected function parseSwitches(array $switches)
    {
        parent::parseSwitches($switches);

        if (!isset($switches['p'])) {
            throw new Exception('No path specified');
        } else {
            $this->path = (string)$switches['p'];
        }

        if (!isset($switches['t'])) {
            throw new Exception('No threshold seconds specified');
        } else {
            $this->thresholdSeconds = (int)$switches['t'];
        }

        if ($this->thresholdSeconds <= 0) {
            throw new Exception('Invalid threshold seconds');
        }
    }

    protected function execute()
    {
        if (!file_exists($this->path)) {
            $this->fail('No such file: ' . $this->path);
            return;
        }

        $fileData               = $this->getFileContents();
        $threshold              = time() - $this->thresholdSeconds;
        $fileFormattedDate      = CarbonImmutable::createFromTimestamp($fileData)->toIso8601String();
        $thresholdFormattedDate = CarbonImmutable::createFromTimestamp($threshold)->toIso8601String();

        if ($threshold > $fileData) {
            $this->fail(
                $this->path . ' contains timestamp of ' . $fileData . ' (' . $fileFormattedDate . '), threshold is '
                    . $threshold . ' (' . $thresholdFormattedDate . ')'
            );
            return;
        }

        echo CarbonImmutable::now()->toIso8601String() . ' - OK: ' . $fileData . ' (' . $fileFormattedDate . ')' . PHP_EOL;
    }

    protected function getFileContents(): int
    {
        // Open the file, ensure that we can get a shared lock on it
        $handle = fopen($this->path, 'r');
        flock($handle, LOCK_SH);

        // Read the file separately
        $fileData = file_get_contents($this->path, false, null, 0, 20);

        // Close the lock handle
        fclose($handle);

        return (int)trim($fileData);
    }

    protected function fail($message)
    {
        $this->setExitCode(self::EXIT_CODE_RUNTIME_ERROR);
        echo CarbonImmutable::now()->toIso8601String() . ' - FAIL: ' . $message . PHP_EOL;
    }

    protected function abort()
    {
        throw new AbortException();
    }

    protected function getScriptDescription()
    {
        return 'Checks when a batch script has last run and exits with code 0 if it ';
    }

}
