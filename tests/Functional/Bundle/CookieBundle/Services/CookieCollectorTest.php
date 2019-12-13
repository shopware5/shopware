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

namespace Shopware\Tests\Functional\Bundle\CookieBundle\Services;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\CookieBundle\CookieCollection;
use Shopware\Bundle\CookieBundle\CookieGroupCollection;
use Shopware\Bundle\CookieBundle\Exceptions\InvalidCookieGroupItemException;
use Shopware\Bundle\CookieBundle\Exceptions\InvalidCookieItemException;
use Shopware\Bundle\CookieBundle\Services\CookieCollector;
use Shopware\Bundle\CookieBundle\Structs\CookieGroupStruct;
use Shopware\Bundle\CookieBundle\Structs\CookieStruct;

class CookieCollectorTest extends TestCase
{
    protected function tearDown(): void
    {
        Shopware()->Container()->get('events')->reset();
    }

    public function testCollect(): void
    {
        $cookieCollector = $this->getCookieCollector();

        /** @var CookieGroupCollection $collection */
        $collection = $cookieCollector->collect();

        // Added default groups
        static::assertSame(5, $collection->count());

        // Added cookies and they were assigned to their group
        static::assertSame(10, $collection->getGroupByName(CookieGroupStruct::TECHNICAL)->getCookies()->count());
        static::assertSame(2, $collection->getGroupByName(CookieGroupStruct::STATISTICS)->getCookies()->count());
    }

    public function testCollectThrowsExceptionInvalidCookieType(): void
    {
        $eventHandler = new \Enlight_Event_Handler_Default(
            'CookieCollector_Collect_Cookies',
            [ExampleTestSubscriber::class, 'addInvalidCookie']
        );

        Shopware()->Container()->get('events')->registerListener($eventHandler);

        $this->expectException(InvalidCookieItemException::class);

        $this->getCookieCollector()->collect();
    }

    public function testCollectWorksWithCustomCookies(): void
    {
        Shopware()->Container()->get('events')->addListener(
            'CookieCollector_Collect_Cookies',
            [ExampleTestSubscriber::class, 'addValidCookie']
        );

        $cookieGroupCollection = $this->getCookieCollector()->collect();

        $cookieCollection = $cookieGroupCollection->getGroupByName(CookieGroupStruct::PERSONALIZATION)->getCookies();

        // Added cookie and it was assigned to its group
        static::assertSame(1, $cookieCollection->count());
        static::assertSame('foo', $cookieCollection->offsetGet(0)->getName());
    }

    public function testCollectCookieGroupsThrowsExceptionInvalidCookieGroupCollection(): void
    {
        Shopware()->Container()->get('events')->addListener(
            'CookieCollector_Collect_Cookie_Groups',
            [ExampleTestSubscriber::class, 'addInvalidCookieGroup']
        );
        $this->expectException(InvalidCookieGroupItemException::class);

        $this->getCookieCollector()->collectCookieGroups();
    }

    public function testCollectCookieGroupsWorksWithCustomGroups(): void
    {
        Shopware()->Container()->get('events')->addListener(
            'CookieCollector_Collect_Cookie_Groups',
            [ExampleTestSubscriber::class, 'addValidCookieGroup']
        );

        $cookieGroupCollection = $this->getCookieCollector()->collectCookieGroups();

        static::assertSame(6, $cookieGroupCollection->count());
    }

    private function getCookieCollector(): CookieCollector
    {
        return new CookieCollector(
            Shopware()->Container()->get('events'),
            Shopware()->Container()->get('snippets'),
            Shopware()->Container()->get('config')
        );
    }
}

class ExampleTestSubscriber
{
    public function addInvalidCookie(): CookieCollection
    {
        $cookieCollection = new CookieCollection();
        $cookieCollection->add(new class() {
        });

        return $cookieCollection;
    }

    public function addValidCookie(): CookieCollection
    {
        $cookieCollection = new CookieCollection();
        $cookieCollection->add(new CookieStruct(
            'foo',
            '/^foo$/',
            'bar',
            CookieGroupStruct::PERSONALIZATION
        ));

        return $cookieCollection;
    }

    public function addInvalidCookieGroup(): CookieGroupCollection
    {
        $cookieGroupCollection = new CookieGroupCollection();
        $cookieGroupCollection->add(new class() {
        });

        return $cookieGroupCollection;
    }

    public function addValidCookieGroup(): CookieGroupCollection
    {
        $cookieGroupCollection = new CookieGroupCollection();
        $cookieGroupCollection->add(new CookieGroupStruct(
            'anotherGroup',
            'anotherGroupLabel'
        ));

        return $cookieGroupCollection;
    }
}
