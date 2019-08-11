<?php
declare(strict_types = 1);


namespace Test\Unit\Test\Integration\DbHelperAbstract;


class GetFromArrayTest extends DbHelperAbstractTestAbstract
{

    public function testWhenKeyDoesNotExist_shouldReturnDefaultValue(): void
    {
        $input = [
            'key' => 'value'
        ];
        $key = 'nonExistentKey';
        $defaultValue = 'default';

        $result = $this->object->getFromArray($input, $key, $defaultValue);

        $this->assertEquals($defaultValue, $result);
    }


    public function testWhenKeyExists_shouldReturnFormattedValue(): void
    {
        $input = [
            'key' => '12.12'
        ];
        $key = 'key';

        $result = $this->object->getFromArray($input, $key);

        $this->assertSame(12.12, $result);
    }
}
