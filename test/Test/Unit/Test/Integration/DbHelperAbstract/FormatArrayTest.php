<?php
declare(strict_types = 1);


namespace Test\Unit\Test\Integration\DbHelperAbstract;


class FormatArrayTest extends DbHelperAbstractTestAbstract
{

    public function testWhenCalled_shouldFormatTheWholeArray()
    {
        $input = [
            [
                'integer in string' => '12',
                'null'              => '<null>'
            ]
        ];

        $expectedResult = [
            [
                'integer in string' => 12,
                'null'              => null
            ]
        ];

        $this->object->formatArray($input);

        $this->assertSame($expectedResult, $input);
    }
}
