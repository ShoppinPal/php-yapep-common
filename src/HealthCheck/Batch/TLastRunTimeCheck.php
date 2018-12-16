<?php
namespace ShoppinPal\YapepCommon\HealthCheck\Batch;

trait TLastRunTimeCheck
{
    protected function updateHealthCheckFile($path)
    {
        file_put_contents($path, time(), LOCK_EX);
    }
}
