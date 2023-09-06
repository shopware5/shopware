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
                (1111111113, 'SW2001', '2010-10-04 10:46:54', 'test@example.org', 1, '1', 'http://example.com/');";
        Shopware()->Db()->query($sql);
    }

    /**
     * Cleaning up testData
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $sql = 'DELETE FROM s_articles_notification WHERE id IN (1111111111, 1111111112, 1111111113)';
        Shopware()->Db()->query($sql);
    }

    /**
     * test getList controller action
     */
    public function testGetArticleList()
    {
        $this->dispatch('backend/Notification/getArticleList');
        static::assertTrue($this->View()->success);
        $returnData = $this->View()->data;
        static::assertNotEmpty($returnData);
        static::assertCount(2, $returnData);
        $listingFirstEntry = $returnData[0];

        // cause of the DataSet you can assert fix values
        static::assertEquals(2, $listingFirstEntry['registered']);
        static::assertEquals('SW2001', $listingFirstEntry['number']);
        static::assertEquals(1, $listingFirstEntry['notNotified']);
    }

    /**
     * test getCustomerList controller action
     */
    public function testGetCustomerList()
    {
        $params['orderNumber'] = 'SW2001';
        $this->Request()->setParams($params);
        $this->dispatch('backend/Notification/getCustomerList');
        static::assertTrue($this->View()->getAssign('success'));

        $returnData = $this->View()->getAssign('data');
        static::assertCount(2, $returnData);
        $listingFirstEntry = $returnData[0];
        $listingSecondEntry = $returnData[1];

        // cause of the DataSet you can assert fix values
        static::assertEquals('test@example.de', $listingFirstEntry['mail']);
        static::assertEquals(0, $listingFirstEntry['notified']);

        static::assertEquals('test@example.org', $listingSecondEntry['mail']);
        static::assertEquals(1, $listingSecondEntry['notified']);

        $params['orderNumber'] = 'SW2003';
        $this->Request()->setParams($params);
        $this->dispatch('backend/Notification/getCustomerList');
        static::assertTrue($this->View()->getAssign('success'));

        $returnData = $this->View()->getAssign('data');

        static::assertCount(1, $returnData);
        static::assertEquals('test@example.com', $returnData[0]['mail']);
        static::assertNotEmpty($returnData[0]['name']);
        static::assertNotEmpty($returnData[0]['customerId']);
    }
}
