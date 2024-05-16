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

namespace Shopware\Tests\Functional\Controllers\Backend;

use Enlight_Components_Test_Controller_TestCase;

class NotificationTest extends Enlight_Components_Test_Controller_TestCase
{
    private const TEST_PRODUCT_LIST_LIMIT = 2;
    private const TEST_PRODUCT_LIST_FIRST_PAGE_OFFSET = 0;
    private const TEST_PRODUCT_LIST_SECOND_PAGE_OFFSET = 2;
    private const TEST_PRODUCT_LIST_TOTAL_RESULTS = 3;
    private const TEST_PRODUCT_LIST_FIRST_PAGE_ITEMS_COUNT = 2;
    private const TEST_PRODUCT_LIST_SECOND_PAGE_ITEMS_COUNT = 1;

    private const TEST_CUSTOMER_LIST_LIMIT = 2;
    private const TEST_CUSTOMER_LIST_FIRST_PAGE_OFFSET = 0;
    private const TEST_CUSTOMER_LIST_SECOND_PAGE_OFFSET = 2;
    private const TEST_CUSTOMER_LIST_TOTAL_RESULTS = 3;
    private const TEST_CUSTOMER_LIST_FIRST_PAGE_ITEMS_COUNT = 2;
    private const TEST_CUSTOMER_LIST_SECOND_PAGE_ITEMS_COUNT = 1;

    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp(): void
    {
        parent::setUp();
        // Disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();

        $sql = "INSERT IGNORE INTO `s_articles_notification` (`id`, `ordernumber`, `date`, `mail`, `send`, `language`, `shopLink`) VALUES
                (1111111111, 'SW2001', '2010-10-04 10:46:56', 'test@example.de', 0, '1', 'http://example.com/'),
                (1111111112, 'SW2003', '2010-10-05 10:46:55', 'test@example.com', 1, '1', 'http://example.com/'),
                (1111111113, 'SW2001', '2010-10-04 10:46:54', 'test@example.org', 1, '1', 'http://example.com/'),
                (1111111114, 'SW2005', '2024-03-27 10:00:00', 'test@example.org', 1, '1', 'http://example.com/'),
                (1111111115, 'SW2001', '2024-03-27 10:01:00', 'john@example.org', 1, '1', 'http://example.com/')
       ;";
        Shopware()->Db()->query($sql);
    }

    /**
     * Cleaning up testData
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $sql = 'DELETE FROM s_articles_notification WHERE id IN (1111111111, 1111111112, 1111111113, 1111111114, 1111111115)';
        Shopware()->Db()->query($sql);
    }

    /**
     * test getList controller action
     */
    public function testGetArticleListFirstPage(): void
    {
        $parameter = [
            'limit' => self::TEST_PRODUCT_LIST_LIMIT,
            'start' => self::TEST_PRODUCT_LIST_FIRST_PAGE_OFFSET,
        ];
        $getParameter = http_build_query($parameter);

        $this->dispatch('backend/Notification/getArticleList?' . $getParameter);
        static::assertTrue($this->View()->getAssign('success'));
        $returnData = $this->View()->getAssign('data');
        static::assertNotEmpty($returnData);

        static::assertCount(self::TEST_PRODUCT_LIST_FIRST_PAGE_ITEMS_COUNT, $returnData);
        static::assertSame(self::TEST_PRODUCT_LIST_TOTAL_RESULTS, $this->View()->getAssign('totalCount'));
        $listingFirstEntry = $returnData[0];

        // cause of the DataSet you can assert fix values
        static::assertSame(3, $listingFirstEntry['registered']);
        static::assertSame('SW2001', $listingFirstEntry['number']);
        static::assertSame('1', $listingFirstEntry['notNotified']);
    }

    /**
     * test getList controller action
     */
    public function testGetArticleListSecondPage(): void
    {
        $parameter = [
            'limit' => self::TEST_PRODUCT_LIST_LIMIT,
            'start' => self::TEST_PRODUCT_LIST_SECOND_PAGE_OFFSET,
        ];
        $getParameter = http_build_query($parameter);

        $this->dispatch('backend/Notification/getArticleList?' . $getParameter);
        static::assertTrue($this->View()->getAssign('success'));
        $returnData = $this->View()->getAssign('data');
        static::assertNotEmpty($returnData);

        static::assertCount(self::TEST_PRODUCT_LIST_SECOND_PAGE_ITEMS_COUNT, $returnData);
        static::assertSame(self::TEST_PRODUCT_LIST_TOTAL_RESULTS, $this->View()->getAssign('totalCount'));
        $listingFirstEntry = $returnData[0];

        static::assertSame(1, $listingFirstEntry['registered']);
        static::assertSame('SW2005', $listingFirstEntry['number']);
        static::assertSame('0', $listingFirstEntry['notNotified']);
    }

    /**
     * test getCustomerList controller action
     */
    public function testGetCustomerList(): void
    {
        $params = [
            'orderNumber' => 'SW2001',
            'limit' => self::TEST_CUSTOMER_LIST_LIMIT,
            'start' => self::TEST_CUSTOMER_LIST_FIRST_PAGE_OFFSET,
        ];

        $this->Request()->setParams($params);
        $this->dispatch('backend/Notification/getCustomerList');
        static::assertTrue($this->View()->getAssign('success'));

        $totalCount = $this->View()->getAssign('totalCount');
        static::assertSame(self::TEST_CUSTOMER_LIST_TOTAL_RESULTS, $totalCount);

        $returnData = $this->View()->getAssign('data');
        static::assertCount(self::TEST_CUSTOMER_LIST_FIRST_PAGE_ITEMS_COUNT, $returnData);
        $listingFirstEntry = $returnData[0];
        $listingSecondEntry = $returnData[1];

        // cause of the DataSet you can assert fix values
        static::assertSame('test@example.de', $listingFirstEntry['mail']);
        static::assertSame(0, $listingFirstEntry['notified']);

        static::assertSame('test@example.org', $listingSecondEntry['mail']);
        static::assertSame(1, $listingSecondEntry['notified']);

        $params['orderNumber'] = 'SW2003';
        $this->Request()->setParams($params);
        $this->dispatch('backend/Notification/getCustomerList');
        static::assertTrue($this->View()->getAssign('success'));

        $returnData = $this->View()->getAssign('data');

        static::assertCount(1, $returnData);
        static::assertSame('test@example.com', $returnData[0]['mail']);
        static::assertNotEmpty($returnData[0]['name']);
        static::assertNotEmpty($returnData[0]['customerId']);
    }

    public function testGetCustomerListSecondPage(): void
    {
        $params = [
            'orderNumber' => 'SW2001',
            'limit' => self::TEST_CUSTOMER_LIST_LIMIT,
            'start' => self::TEST_CUSTOMER_LIST_SECOND_PAGE_OFFSET,
        ];

        $this->Request()->setParams($params);
        $this->dispatch('backend/Notification/getCustomerList');
        static::assertTrue($this->View()->getAssign('success'));

        $totalCount = $this->View()->getAssign('totalCount');
        static::assertSame(self::TEST_CUSTOMER_LIST_TOTAL_RESULTS, $totalCount);

        $returnData = $this->View()->getAssign('data');
        static::assertCount(self::TEST_CUSTOMER_LIST_SECOND_PAGE_ITEMS_COUNT, $returnData);
        $listingFirstEntry = $returnData[0];

        static::assertSame('john@example.org', $listingFirstEntry['mail']);
        static::assertSame(1, $listingFirstEntry['notified']);
    }
}
