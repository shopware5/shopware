<?php

namespace Shopware\Tests\Service;

class CloneStructTest extends TestCase
{
    public function testNestedStructCloning()
    {
        $simple = new SimpleStruct(
            new SimpleStruct('initial')
        );

        $clone = clone $simple;
        $simple->setValue('modified');

        $this->assertInstanceOf('\Shopware\Tests\Service\SimpleStruct', $clone->getValue());
        $this->assertEquals('initial', $clone->getValue()->getValue());
    }

    public function testNestedArrayCloning()
    {
        $simple = new SimpleStruct(
           [
               new SimpleStruct('struct 1'),
               new SimpleStruct('struct 2')
           ]
        );

        $clone = clone $simple;

        /**@var $nested SimpleStruct[]*/
        $nested = $simple->getValue();
        $nested[0]->setValue('struct 3');

        $nested = $clone->getValue();
        $this->assertEquals('struct 1', $nested[0]->getValue());
        $this->assertEquals('struct 2', $nested[1]->getValue());

        $simple->setValue('override');
        $this->assertEquals('struct 1', $nested[0]->getValue());
        $this->assertEquals('struct 2', $nested[1]->getValue());
    }

    public function testAssociatedArrayCloning()
    {
        $simple = new SimpleStruct(
            [
                'struct1' => new SimpleStruct('struct 1'),
                'struct2' => new SimpleStruct('struct 2')
            ]
        );

        $clone = clone $simple;
        $simple->setValue(null);

        /**@var $nested SimpleStruct[]*/
        $nested = $clone->getValue();
        $this->assertArrayHasKey('struct1', $nested);
        $this->assertArrayHasKey('struct2', $nested);

        $clone->setValue('test123');
        $this->assertNull($simple->getValue());
    }

    public function testRecursiveArrayCloning()
    {
        $simple = new SimpleStruct(
            [
                [new SimpleStruct('struct 1'), new SimpleStruct('struct 1')],
                [new SimpleStruct('struct 2'), new SimpleStruct('struct 2')]
            ]
        );

        $clone = clone $simple;
        $simple->setValue(null);

        /**@var $value SimpleStruct[]*/
        $value = $clone->getValue();
        $this->assertCount(2, $value[0]);
        $this->assertCount(2, $value[1]);

        $this->assertEquals('struct 1', $value[0][0]->getValue());
    }
}