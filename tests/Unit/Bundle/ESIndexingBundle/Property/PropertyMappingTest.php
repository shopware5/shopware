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

namespace Shopware\Tests\Unit\Bundle\ESIndexingBundle\Property;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\ESIndexingBundle\FieldMapping;
use Shopware\Bundle\ESIndexingBundle\Property\PropertyMapping;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;

class PropertyMappingTest extends TestCase
{
    public function testDynamicIsTrueInDefault()
    {
        $fieldMapping = $this->getMockBuilder(FieldMapping::class)->disableOriginalConstructor()->getMock();
        $shop = $this->getMockBuilder(Shop::class)->disableOriginalConstructor()->getMock();

        $propertyMapping = new PropertyMapping($fieldMapping);

        $mapping = $propertyMapping->get($shop);

        static::assertTrue($mapping['dynamic']);
    }

    public function testDynamicIsFalseWhenFalseIsPassed()
    {
        $fieldMapping = $this->getMockBuilder(FieldMapping::class)->disableOriginalConstructor()->getMock();
        $shop = $this->getMockBuilder(Shop::class)->disableOriginalConstructor()->getMock();

        $propertyMapping = new PropertyMapping($fieldMapping, false);

        $mapping = $propertyMapping->get($shop);

        static::assertFalse($mapping['dynamic']);
    }

    public function testDynamicIsTrueWhenTrueIsPassed()
    {
        $fieldMapping = $this->getMockBuilder(FieldMapping::class)->disableOriginalConstructor()->getMock();
        $shop = $this->getMockBuilder(Shop::class)->disableOriginalConstructor()->getMock();

        $propertyMapping = new PropertyMapping($fieldMapping, true);

        $mapping = $propertyMapping->get($shop);

        static::assertTrue($mapping['dynamic']);
    }
}
