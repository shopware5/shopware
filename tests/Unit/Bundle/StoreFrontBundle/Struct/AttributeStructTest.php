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

namespace Shopware\Tests\Unit\StoreFrontBundle\Struct;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;

class AttributeStructTest extends TestCase
{
    /**
     * @dataProvider getDataProvider
     *
     * @param $value
     *
     * @throws \Exception
     */
    public function testWithValidValues($value)
    {
        $attr = new Attribute(['attr' => $value]);
        $this->assertTrue($attr->exists('attr'));
        $this->assertSame($value, $attr->get('attr'));
        $attr->set('attr', $value);
        $this->assertSame($value, $attr->get('attr'));
        $this->assertSame($attr->toArray(), $attr->jsonSerialize());
        $json = json_encode(['attr' => $value]);
        $this->assertNotFalse($json);
        $this->assertSame(json_encode($attr), $json);
    }

    /**
     * @dataProvider getInvalidDataProvider
     *
     * @param $value
     *
     * @throws \InvalidArgumentException
     */
    public function testWithInvalidValues($value)
    {
        $this->expectException(\InvalidArgumentException::class);
        new Attribute(['attr' => $value]);
    }

    /**
     * @dataProvider getInvalidDataProvider
     *
     * @param $value
     *
     * @throws \InvalidArgumentException
     */
    public function testWithInvalidValuesSet($value)
    {
        $this->expectException(\InvalidArgumentException::class);
        $attr = new Attribute(['attr' => null]);
        $attr->set('attr', $value);
    }

    /***
     * @throws \InvalidArgumentException
     */
    public function testWithInvalidData()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Attribute(null);
    }

    public function getDataProvider()
    {
        return [
            [0],
            [null],
            ['test'],
            [0.99],
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
