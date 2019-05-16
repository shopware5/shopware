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
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;

class AttributeStructTest extends TestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testWithValidValues($value)
    {
        $attr = new Attribute(['attr' => $value]);
        static::assertTrue($attr->exists('attr'));
        static::assertSame($value, $attr->get('attr'));

        $attr->set('attr', $value);
        static::assertSame($value, $attr->get('attr'));
        static::assertSame($attr->toArray(), $attr->jsonSerialize());

        $json = json_encode(['attr' => $value]);
        static::assertNotFalse($json);
        static::assertSame(json_encode($attr), $json);
    }

    /**
     * @dataProvider getInvalidDataProvider
     */
    public function testWithInvalidValues($value)
    {
        $this->expectException(\InvalidArgumentException::class);

        new Attribute(['attr' => $value]);
    }

    /**
     * @dataProvider getInvalidDataProvider
     */
    public function testWithInvalidValuesSet($value)
    {
        $this->expectException(\InvalidArgumentException::class);

        $attr = new Attribute(['attr' => null]);
        $attr->set('attr', $value);
    }

    public function testWithNullData()
    {
        $this->expectException(\TypeError::class);
        new Attribute(null);
    }

    public function testWithObjectData()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Attribute([new \ArrayIterator([])]);
    }

    public function getDataProvider()
    {
        return [
            [0],
            [[]],
            [null],
            ['test'],
            [0.99],
            [['test' => 'foo']],
            [[2 => 'foo']],
            [[2 => ['foo' => 'bar']]],
            [['bar' => ['foo' => 'bar']]],
            [['test' => new Attribute(['bar' => 'foo'])]],
        ];
    }

    public function getInvalidDataProvider()
    {
        return [
            [fopen('php://memory', 'rw')],
            [new \ArrayIterator([])],
            [new \Exception()],
        ];
    }
}
