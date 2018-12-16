<?php
namespace ShoppinPal\YapepCommon\HealthCheck\Batch;

trait TLastRunTimeCheck
{
    protected function updateHealthCheckFile($path)
    {
        if (!file_exists(basename($path))) {
            mkdir(basename($path), 0755, true);
        }

        file_put_contents($path, time(), LOCK_EX);
    }
}
