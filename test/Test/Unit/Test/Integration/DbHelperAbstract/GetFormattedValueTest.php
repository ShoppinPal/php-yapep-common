<?php
declare(strict_types = 1);


namespace Test\Unit\Test\Integration\DbHelperAbstract;


class GetFormattedValueTest extends DbHelperAbstractTestAbstract
{

    public function testWhenNullStringGiven_shouldReturnNull(): void
    {
        $result = $this->object->getFormattedValue('<null>');
        $this->assertNull($result);
    }


    public function numericDataProvider(): array
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
    public function testWhenNumericValueGiven_shouldCastToProperType($value, $expectedResult): void
    {
        $result = $this->object->getFormattedValue($value);
        $this->assertSame($expectedResult, $result);
    }


    public function testWhenDateNowStringGiven_shouldReturnProperDate(): void
    {
        $this->expectGetCurrentTimestamp(1490286701);
        $expectedResult = '2017-03-23';

        $result = $this->object->getFormattedValue('<DATE: NOW>');

        $this->assertEquals($expectedResult, $result);
    }

    public function testWhenDateStringGiven_shouldReturnProperDate(): void
    {
        $this->expectGetCurrentTimestamp(1490286701);
        $expectedResult = '2017-03-25';

        $result = $this->object->getFormattedValue('<DATE: +2 DAYS>');

        $this->assertEquals($expectedResult, $result);
    }

    public function testWhenDateTimeNowStringGiven_shouldReturnProperDate(): void
    {
        $this->expectGetCurrentTimestamp(1490286701);
        $expectedResult = '2017-03-23 16:31:41';

        $result = $this->object->getFormattedValue('<DATETIME: NOW>');

        $this->assertEquals($expectedResult, $result);
    }

    public function testWhenDateTimeStringGiven_shouldReturnProperDate(): void
    {
        $this->expectGetCurrentTimestamp(1490286701);
        $expectedResult = '2017-03-25 16:31:41';

        $result = $this->object->getFormattedValue('<DATETIME: +2 DAYS>');

        $this->assertEquals($expectedResult, $result);
    }

    public function otherValueProvider(): array
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
    public function testWhenOtherValueGiven_shouldReturnItUnchanged($inputAndExpectedValue): void
    {
        $result = $this->object->getFormattedValue($inputAndExpectedValue);

        $this->assertSame($inputAndExpectedValue, $result);
    }
}
