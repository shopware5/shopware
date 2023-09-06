<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Unit;

use Enlight_Collection_ArrayCollection as ArrayCollection;
use PHPUnit\Framework\TestCase;

class ArrayCollectionTest extends TestCase
{
    /**
     * Test case method
     */
    public function testArrayCollectionGet()
    {
        $collection = new ArrayCollection([
            'key_one' => 'wert1',
            'key_two' => 'wert2',
        ]);

        static::assertEquals('wert1', $collection->key_one);
        static::assertEquals('wert1', $collection->getKeyOne());
        static::assertEquals('wert1', $collection->get('key_one'));
    }

    /**
     * Test case method
     */
    public function testArrayCollectionSet()
    {
        $collection = new ArrayCollection();

        $collection->setKeyOne('wert123');
        static::assertEquals('wert123', $collection->getKeyOne());

        $collection->key_one = 'wert145';
        static::assertEquals('wert145', $collection->key_one);
    }
}
