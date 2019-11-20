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

namespace Shopware\Tests\Models;

use Shopware\Models\Order\Order;
use Shopware\Models\Shop\Shop;

class ShopRepositoryTest extends \Enlight_Components_Test_Controller_TestCase
{
    /**
     * @var \Shopware\Models\Shop\Repository
     */
    private $shopRepository;

    private $mainShop;

    private $mainShopBackup;

    public function setUp(): void
    {
        parent::setUp();

        $this->shopRepository = Shopware()->Models()->getRepository(Shop::class);
        $this->mainShop = Shopware()->Db()->fetchRow('SELECT * FROM s_core_shops WHERE id = 1');

        // Backup and change existing main shop
        $this->mainShopBackup = Shopware()->Db()->fetchRow('SELECT * FROM s_core_shops WHERE id = 1');

        Shopware()->Db()->update('s_core_shops', [
            'host' => 'fallbackhost',
        ], 'id = 1');

        $this->mainShop = Shopware()->Db()->fetchRow('SELECT * FROM s_core_shops WHERE id = 1');

        // Create test shops
        $sql = "
            INSERT IGNORE INTO `s_core_shops` (`id`, `main_id`, `name`, `title`, `position`, `host`, `base_path`, `base_url`, `hosts`, `secure`, `template_id`, `document_template_id`, `category_id`, `locale_id`, `currency_id`, `customer_group_id`, `fallback_id`, `customer_scope`, `default`, `active`) VALUES
            (100, 1, 'testShop1', 'Testshop', 0, NULL, NULL, ?, '', 0, 11, 11, 11, 2, 1, 1, 2, 0, 0, 1),
            (101, 1, 'testShop2', 'Testshop', 0, NULL, NULL, ?, '', 0, 11, 11, 11, 2, 1, 1, 2, 0, 0, 1),
            (102, 1, 'testShop3', 'Testshop', 0, NULL, NULL, ?, '', 0, 11, 11, 11, 2, 1, 1, 2, 0, 0, 1),
            (103, 1, 'testShop4', 'Testshop', 0, NULL, NULL, ?, '', 0, 11, 11, 11, 2, 1, 1, 2, 0, 0, 1),
            (104, 1, 'testShop5', 'Testshop', 0, NULL, NULL, ?, '', 0, 11, 11, 11, 2, 1, 1, 2, 0, 0, 1);

        ";
        Shopware()->Db()->query($sql, [
            $this->mainShop['base_path'] . '/english',
            $this->mainShop['base_path'] . '/en/uk',
            $this->mainShop['base_path'] . '/en',
            $this->mainShop['base_path'] . '/en/us',
            $this->mainShop['base_path'] . '/aus/en',
        ]);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        // Remove test data and restore previous status
        Shopware()->Db()->exec('DELETE FROM s_core_shops WHERE id IN (100, 101, 102, 103, 104);');
        unset($this->mainShopBackup['id']);
        Shopware()->Db()->update('s_core_shops', $this->mainShopBackup, 'id = 1');
    }

    /**
     * Ensures that getActiveByRequest() returns the correct shop
     *
     * @ticket SW-7774
     * @ticket SW-6768
     */
    public function testGetActiveByRequest()
    {
        // Tests copied for SW-6768
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/en', 'testShop3');

        //check virtual url with superfluous / like localhost/en/
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/en/', 'testShop3');

        //check virtual url with direct controller call like localhost/en/blog
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/en/blog', 'testShop3');

        //check base shop with direct controller call like localhost/en/blog
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/blog', $this->mainShop['name']);

        //check without virtual url but an url with the same beginning like localhost/entsorgung
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/entsorgung', $this->mainShop['name']);

        //check different virtual url with like localhost/ente
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/en/uk', 'testShop2');

        //check without virtual url it has to choose the main shop instead of the language shop without the virtual url
        $this->callGetActiveShopByRequest($this->mainShop['base_path'], $this->mainShop['name']);

        // These are just some basic urls
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '', $this->mainShop['name']);
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/', $this->mainShop['name']);
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/foo/en', $this->mainShop['name']);
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/foo/entsorgung', $this->mainShop['name']);
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/fenglish', $this->mainShop['name']);
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/english', 'testShop1');
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/en', 'testShop3');

        // These cover the cases affected by the ticket, where the base_path would be present in the middle of the url
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/foo/english', $this->mainShop['name']);
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/foo/en', $this->mainShop['name']);
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/foo/enaaa/', $this->mainShop['name']);
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/foo/uk/', $this->mainShop['name']);
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/foo/en/uk/', $this->mainShop['name']);
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/foo/en/uk/things', $this->mainShop['name']);

        // And these are some extreme cases, due to the overlapping of urls
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/en/ukfoooo', 'testShop3');
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/en/uk', 'testShop2');
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/en', 'testShop3');
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/en/uk/things', 'testShop2');

        // Tests for secure
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/en/us', 'testShop4', true);
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/en/us', 'testShop4', false);
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/en/ukfoooo', 'testShop3', true);
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/en/ukfoooo', 'testShop3', false);
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/en/uk', 'testShop2', true);
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/en/uk', 'testShop2', false);
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/en/uk/things', 'testShop2', true);
        $this->callGetActiveShopByRequest($this->mainShop['base_path'] . '/en/uk/things', 'testShop2', false);
    }

    /**
     * helper method to call the getActiveByRequest Method with different params
     *
     * @param string $url
     * @param string $shopName
     * @param bool   $secure
     */
    public function callGetActiveShopByRequest($url, $shopName, $secure = false)
    {
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setHttpHost($this->mainShop['host']);
        $request->setRequestUri($url);
        $request->setSecure($secure);

        $shop = $this->shopRepository->getActiveByRequest($request);

        static::assertNotNull($shop);
        static::assertEquals($shopName, $shop->getName());
    }

    public function getMultiShopLocationTestData()
    {
        return [
            ['test.in', 'fr.test.in'],
            ['test.in', 'nl.test.in'],
            ['2test.in', '2fr.test.in'],
            ['2test.in', '2nl.test.in'],
        ];
    }

    /**
     * @dataProvider getMultiShopLocationTestData
     * @ticket SW-4858
     */
    public function testMultiShopLocation($host, $alias)
    {
        Shopware()->Container()->reset('template');

        // Create test shops
        $sql = "
            INSERT IGNORE INTO `s_core_shops` (
              `id`, `main_id`, `name`, `title`, `position`,
              `host`, `base_path`, `base_url`, `hosts`,
              `secure`,
              `template_id`, `document_template_id`, `category_id`,
              `locale_id`, `currency_id`, `customer_group_id`,
              `fallback_id`, `customer_scope`, `default`, `active`
            ) VALUES (
              10, NULL, 'Testshop 2', 'Testshop 2', 0,
              '2test.in', NULL, NULL, '2fr.test.in\\n2nl.test.in\\n',
              0,
              11, 11, 11, 2, 1, 1, 2, 0, 0, 1
            ), (
              11, NULL, 'Testshop 1', 'Testshop 1', 0,
              'test.in', NULL, NULL, 'fr.test.in\\nnl.test.in\\n',
              0,
              11, 11, 11, 2, 1, 1, 2, 0, 0, 1
            );
        ";
        Shopware()->Db()->exec($sql);

        $request = $this->Request();
        $this->Request()->setHttpHost($alias);
        $shop = $this->shopRepository->getActiveByRequest($request);

        static::assertNotNull($shop);
        static::assertEquals($host, $shop->getHost());

        // Delete test shops
        $sql = 'DELETE FROM s_core_shops WHERE id IN (10, 11);';
        Shopware()->Db()->exec($sql);
    }

    /**
     * Tests the shop duplication bug caused by the detaching the shop entity
     * in the obsolete Shopware\Models\Shop\Repository::fixActive()
     */
    public function testShopDuplication()
    {
        // Get inital number of shops
        $numberOfShopsBefore = Shopware()->Db()->fetchOne('SELECT count(*) FROM s_core_shops');

        // Load arbitrary order
        $order = Shopware()->Models()->getRepository(Order::class)->find(57);

        // Modify order entitiy to trigger an update action, when the entity is flushed to the database
        $order->setComment('Dummy');

        // Send order status mail to customer, this will invoke the fixActive()-method
        $mail = Shopware()->Modules()->Order()->createStatusMail($order->getId(), 7);
        Shopware()->Modules()->Order()->sendStatusMail($mail);

        // Flush changes changed order to the database
        Shopware()->Models()->flush($order);

        // Get current number of shops
        $numberOfShopsAfter = Shopware()->Db()->fetchOne('SELECT count(*) FROM s_core_shops');

        // Check that the number of shops has not changed
        static::assertSame($numberOfShopsBefore, $numberOfShopsAfter);

        // Clean up comment
        $order->setComment('');
        Shopware()->Models()->flush($order);
    }

    /**
     * Test Shopware\Models\Shop\Repository::getById() and getActiveById()
     */
    public function testRetrieveInactiveSubshop()
    {
        // Create test shops
        $sql = "
            INSERT IGNORE INTO `s_core_shops` (
              `id`, `main_id`, `name`, `title`, `position`,
              `host`, `base_path`, `base_url`, `hosts`, `secure`,
              `template_id`, `document_template_id`, `category_id`,
              `locale_id`, `currency_id`, `customer_group_id`,
              `fallback_id`, `customer_scope`, `default`, `active`
            ) VALUES (
              12, NULL, 'Testshop Active', 'Testshop Active', 0,
              'activetest.in', NULL, NULL, '', 0,
              11, 11, 11, 2, 1, 1, 2, 0, 0, 1
            ), (
              13, NULL, 'Testshop Inactive', 'Testshop Inactive', 0,
              'inactivetest.in', NULL, NULL, '', 0,
              11, 11, 11, 2, 1, 1, 2, 0, 0, 0
            );
        ";
        Shopware()->Db()->exec($sql);

        // Only active shops
        static::assertNotNull($this->shopRepository->getActiveById(12));
        static::assertNull($this->shopRepository->getActiveById(13));

        // Also inactive shops
        static::assertNotNull($this->shopRepository->getById(12));
        static::assertNotNull($this->shopRepository->getById(13));

        // Delete test shops
        $sql = 'DELETE FROM s_core_shops WHERE id IN (12, 13);';
        Shopware()->Db()->exec($sql);
    }
}
