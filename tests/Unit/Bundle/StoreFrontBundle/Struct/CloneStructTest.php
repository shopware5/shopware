<?php

namespace Shopware\Tests\StoreFrontBundle\Struct;

use Shopware\Bundle\StoreFrontBundle\Struct\Struct as BaseStruct;

class SimpleStruct extends BaseStruct
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}

class CloneStructTest extends \PHPUnit_Framework_TestCase
{
    public function testNestedStructCloning()
    {
        $simple = new SimpleStruct(
            new SimpleStruct('initial')
        );

        $clone = clone $simple;
        $simple->setValue('modified');

        $this->assertInstanceOf(SimpleStruct::class, $clone->getValue());
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
