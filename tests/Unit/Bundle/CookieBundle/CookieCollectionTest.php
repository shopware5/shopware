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

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\CookieBundle\CookieCollection;
use Shopware\Bundle\CookieBundle\Structs\CookieStruct;

class CookieCollectionTest extends TestCase
{
    public function testIsValidReturnsTrue(): void
    {
        $cookieCollection = new CookieCollection();
        $cookieCollection->add(new CookieStruct('foo', '/^foo$/', 'bar'));

        static::assertTrue($cookieCollection->isValid());
    }

    public function testIsValidReturnsFalse(): void
    {
        $cookieCollection = new CookieCollection();
        $cookieCollection->add(new class() {
        });

        static::assertFalse($cookieCollection->isValid());
    }

    public function testHasCookieWithNameReturnsTrue(): void
    {
        $name = 'foo';
        $name2 = 'baz';

        $cookieCollection = new CookieCollection();
        $cookieCollection->add(new CookieStruct($name, '/^' . $name . '$/', 'bar'));
        $cookieCollection->add(new CookieStruct($name2, '/^' . $name2 . '$/', 'bar'));

        static::assertTrue($cookieCollection->hasCookieWithName($name));
        static::assertTrue($cookieCollection->hasCookieWithName($name2));
    }

    public function testHasCookieWithNameReturnsFalse(): void
    {
        $cookieCollection = new CookieCollection();
        $cookieCollection->add(new CookieStruct('baz', '/^baz$/', 'bar'));

        static::assertFalse($cookieCollection->hasCookieWithName('foo'));
    }

    public function testGetCookieByNameReturnsCookie(): void
    {
        $cookieCollection = new CookieCollection();
        $cookieCollection->add(new CookieStruct('baz', '/^baz$/', 'bar'));

        static::assertSame('bar', $cookieCollection->getCookieByName('baz')->getLabel());
    }

    public function testGetCookieByNameReturnsNull(): void
    {
        $cookieCollection = new CookieCollection();
        $cookieCollection->add(new CookieStruct('baz', '/^baz$/', 'bar'));

        static::assertNull($cookieCollection->getCookieByName('baz2'));
    }
}
