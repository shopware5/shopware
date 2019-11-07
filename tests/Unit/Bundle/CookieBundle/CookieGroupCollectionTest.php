<?php declare(strict_types=1);
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

namespace Shopware\Tests\Unit\Bundle\CookieBundle;

use PHPunit\Framework\TestCase;
use Shopware\Bundle\CookieBundle\CookieGroupCollection;
use Shopware\Bundle\CookieBundle\Exceptions\NoCookieGroupByNameKnownException;
use Shopware\Bundle\CookieBundle\Structs\CookieGroupStruct;

class CookieGroupCollectionTest extends TestCase
{
    public function testIsValidReturnsTrue(): void
    {
        $cookieGroupCollection = new CookieGroupCollection();
        $cookieGroupCollection->add(new CookieGroupStruct('foo', 'bar'));

        static::assertTrue($cookieGroupCollection->isValid());
    }

    public function testIsValidReturnsFalse(): void
    {
        $cookieGroupCollection = new CookieGroupCollection();
        $cookieGroupCollection->add(new class() {
        });

        static::assertFalse($cookieGroupCollection->isValid());
    }

    public function testGetGroupByName(): void
    {
        $name1 = 'foo';
        $name2 = 'baz';
        $label1 = 'bar';
        $label2 = 'bar2';

        $cookieGroupCollection = new CookieGroupCollection();
        $cookieGroupCollection->add(new CookieGroupStruct($name1, $label1));
        $cookieGroupCollection->add(new CookieGroupStruct($name2, $label2));

        $group1 = $cookieGroupCollection->getGroupByName($name1);

        static::assertSame($name1, $group1->getName());
        static::assertSame($label1, $group1->getLabel());

        $group2 = $cookieGroupCollection->getGroupByName($name2);

        static::assertSame($name2, $group2->getName());
        static::assertSame($label2, $group2->getLabel());
    }

    public function testGetGroupByNameThrowsException(): void
    {
        $cookieGroupCollection = new CookieGroupCollection();
        $cookieGroupCollection->add(new CookieGroupStruct('foo', 'bar'));

        $this->expectException(NoCookieGroupByNameKnownException::class);

        $cookieGroupCollection->getGroupByName('baz');
    }
}
