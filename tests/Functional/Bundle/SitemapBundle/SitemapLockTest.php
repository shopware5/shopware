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

namespace Shopware\Tests\Functional\Bundle\SitemapBundle;

use Doctrine\Common\Cache\ArrayCache;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\SitemapBundle\Service\SitemapLock;
use Shopware\Models\Shop\Shop;

class SitemapLockTest extends TestCase
{
    public function testLocks()
    {
        $shop = new ShopMock();
        $shop->id = 1;

        $lock = new SitemapLock(new \Shopware\Bundle\StoreFrontBundle\Service\Core\CoreCache(new ArrayCache()), 'sitemap-exporter-running-%s');

        // Check that the Shop is not locked
        static::assertFalse($lock->isLocked($shop), 'Shop already locked');

        // Check we can lock the Shop
        static::assertTrue($lock->doLock($shop, 60), 'Failed to lock shop');

        // Check that is indeed locked
        static::assertTrue($lock->isLocked($shop), 'Check for locked shop failed');

        // Check that we cannot lock the Shop again now
        static::assertFalse($lock->doLock($shop), 'Lock for shop was not persisted');

        // Check we can unlock the shop
        static::assertTrue($lock->unLock($shop), 'Failed to unlock shop');

        // Check that is indeed unlocked
        static::assertFalse($lock->isLocked($shop), 'Shop was not unlocked');

        // Check we can lock the shop again
        static::assertTrue($lock->doLock($shop, 60), 'Failed to lock shop again');

        // Finally unlock Shop again
        static::assertTrue($lock->unLock($shop), 'Failed to unlock shop twice');
    }
}

class ShopMock extends Shop
{
    /**
     * @var int
     */
    public $id;
}
