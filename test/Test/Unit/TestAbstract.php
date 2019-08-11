<?php
declare(strict_types = 1);


namespace Test\Unit;

use Mockery;
use PHPUnit\Framework\TestCase;

class TestAbstract extends TestCase
{

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

}
