<?php
declare(strict_types = 1);


namespace Test\Unit;

use Mockery;

class TestAbstract extends \PHPUnit_Framework_TestCase
{

    protected function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

}
