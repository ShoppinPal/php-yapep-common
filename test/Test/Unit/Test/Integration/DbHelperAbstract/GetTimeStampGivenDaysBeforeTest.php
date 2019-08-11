<?php
declare(strict_types = 1);


namespace Test\Unit\Test\Integration\DbHelperAbstract;


class GetTimeStampGivenDaysBeforeTest extends DbHelperAbstractTestAbstract
{

    public function testWhenCalled_shouldCalculateProperTimeStamp(): void
    {
        $this->expectGetCurrentTimestamp(1490286701);

        $result = $this->object->getTimeStampGivenDaysBefore(10);

        $this->assertEquals(1489422701, $result);
    }
}
