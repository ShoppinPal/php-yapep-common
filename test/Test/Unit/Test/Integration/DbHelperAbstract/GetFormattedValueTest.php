<?php
declare(strict_types = 1);


namespace Test\Unit\Test\Integration\DbHelperAbstract;


class GetFormattedValueTest extends DbHelperAbstractTestAbstract
{

    public function testWhenNullStringGiven_shouldReturnNull()
    {
        $result = $this->object->getFormattedValue('<null>');
        $this->assertNull($result);
    }


    public function numericDataProvider()
    {
        return [
            'integer as string' => ['12', 12],
            'float as string'   => ['12.12', 12.12],
            'zero as string'    => ['0', 0],
        ];
    }

    /**
     * @dataProvider numericDataProvider
     *
     * @param $value
     * @param $expectedResult
     */
    public function testWhenNumericValueGiven_shouldCastToProperType($value, $expectedResult)
    {
        $result = $this->object->getFormattedValue($value);
        $this->assertSame($expectedResult, $result);
    }


    public function testWhenDateNowStringGiven_shouldReturnProperDate()
    {
        $this->expectGetCurrentTimestamp(1490286701);
        $expectedResult = '2017-03-23';

        $result = $this->object->getFormattedValue('<DATE: NOW>');

        $this->assertEquals($expectedResult, $result);
    }

    public function testWhenDateStringGiven_shouldReturnProperDate()
    {
        $this->expectGetCurrentTimestamp(1490286701);
        $expectedResult = '2017-03-25';

        $result = $this->object->getFormattedValue('<DATE: +2 DAYS>');

        $this->assertEquals($expectedResult, $result);
    }

    public function testWhenDateTimeNowStringGiven_shouldReturnProperDate()
    {
        $this->expectGetCurrentTimestamp(1490286701);
        $expectedResult = '2017-03-23 16:31:41';

        $result = $this->object->getFormattedValue('<DATETIME: NOW>');

        $this->assertEquals($expectedResult, $result);
    }

    public function testWhenDateTimeStringGiven_shouldReturnProperDate()
    {
        $this->expectGetCurrentTimestamp(1490286701);
        $expectedResult = '2017-03-25 16:31:41';

        $result = $this->object->getFormattedValue('<DATETIME: +2 DAYS>');

        $this->assertEquals($expectedResult, $result);
    }

    public function otherValueProvider()
    {
        return [
            'simple string' => ['just a string'],
            'integer'       => [12],
            'float'         => [12.12],
            'bool'          => [true]
        ];
    }

    /**
     * @dataProvider otherValueProvider
     *
     * @param $inputAndExpectedValue
     */
    public function testWhenOtherValueGiven_shouldReturnItUnchanged($inputAndExpectedValue)
    {
        $result = $this->object->getFormattedValue($inputAndExpectedValue);

        $this->assertSame($inputAndExpectedValue, $result);
    }
}
