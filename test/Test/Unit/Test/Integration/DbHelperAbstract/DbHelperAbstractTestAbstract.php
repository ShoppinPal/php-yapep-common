<?php
declare(strict_types = 1);

namespace Test\Unit\Test\Integration\DbHelperAbstract;


use Mockery;
use ShoppinPal\YapepCommon\Test\Integration\DbHelperAbstract;
use Test\Unit\TestAbstract;
use YapepBase\Database\DbConnection;

/**
 * Base test class for the unit tests of the DbHelperAbstract.
 */
abstract class DbHelperAbstractTestAbstract extends TestAbstract
{

    /**
     * The object to test.
     *
     * @var DbHelperAbstract
     */
    protected $object;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();
        $this->object = Mockery::mock('ShoppinPal\YapepCommon\Test\Integration\DbHelperAbstract[getCurrentTimestamp,getDbConnection]');

    }


    protected function expectGetCurrentTimestamp(int $expectedResult)
    {
        $this->object
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getCurrentTimestamp')
            ->once()
            ->andReturn($expectedResult);
    }

    protected function expectGetDbConnection(DbConnection $expectedResult)
    {
        $this->object
            ->shouldReceive('getDbConnection')
            ->once()
            ->andReturn($expectedResult);
    }
}
