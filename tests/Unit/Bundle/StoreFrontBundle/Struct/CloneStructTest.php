<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Unit\Bundle\StoreFrontBundle\Struct;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Struct\Struct as BaseStruct;

class SimpleStruct extends BaseStruct
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
}

class CloneStructTest extends TestCase
{
    public function testNestedStructCloning()
    {
        $simple = new SimpleStruct(
            new SimpleStruct('initial')
        );

        $clone = clone $simple;
        $simple->setValue('modified');

        static::assertInstanceOf(SimpleStruct::class, $clone->getValue());
        static::assertEquals('initial', $clone->getValue()->getValue());
    }

    public function testNestedArrayCloning()
    {
        $simple = new SimpleStruct(
            [
               new SimpleStruct('struct 1'),
               new SimpleStruct('struct 2'),
            ]
        );

        $clone = clone $simple;

        /** @var SimpleStruct[] $nested */
        $nested = $simple->getValue();
        $nested[0]->setValue('struct 3');

        $nested = $clone->getValue();
        static::assertEquals('struct 1', $nested[0]->getValue());
        static::assertEquals('struct 2', $nested[1]->getValue());

        $simple->setValue('override');
        static::assertEquals('struct 1', $nested[0]->getValue());
        static::assertEquals('struct 2', $nested[1]->getValue());
    }

    public function testAssociatedArrayCloning()
    {
        $simple = new SimpleStruct(
            [
                'struct1' => new SimpleStruct('struct 1'),
                'struct2' => new SimpleStruct('struct 2'),
            ]
        );

        $clone = clone $simple;
        $simple->setValue(null);

        /** @var SimpleStruct[] $nested */
        $nested = $clone->getValue();
        static::assertArrayHasKey('struct1', $nested);
        static::assertArrayHasKey('struct2', $nested);

        $clone->setValue('test123');
        static::assertNull($simple->getValue());
    }

    public function testRecursiveArrayCloning()
    {
        $simple = new SimpleStruct(
            [
                [new SimpleStruct('struct 1'), new SimpleStruct('struct 1')],
                [new SimpleStruct('struct 2'), new SimpleStruct('struct 2')],
            ]
        );

        $clone = clone $simple;
        $simple->setValue(null);

        /** @var SimpleStruct[][] $value */
        $value = $clone->getValue();
        static::assertCount(2, $value[0]);
        static::assertCount(2, $value[1]);

        static::assertEquals('struct 1', $value[0][0]->getValue());
    }
}
