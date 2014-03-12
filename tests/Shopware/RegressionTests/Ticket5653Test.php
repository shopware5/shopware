<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_RegressionTests_Ticket5653 extends Enlight_Components_Test_Plugin_TestCase
{
    /**
     * Set up test case, fix demo data where needed
     */
    public function setUp()
    {
        parent::setUp();

        // insert test order
        $sql = "
            INSERT IGNORE INTO `s_order` (`id`,`ordernumber`, `userID`, `invoice_amount`, `invoice_amount_net`, `invoice_shipping`, `invoice_shipping_net`, `ordertime`, `status`, `cleared`, `paymentID`, `transactionID`, `comment`, `customercomment`, `internalcomment`, `net`, `taxfree`, `partnerID`, `temporaryID`, `referer`, `cleareddate`, `trackingcode`, `language`, `dispatchID`, `currency`, `currencyFactor`, `subshopID`, `remote_addr`) VALUES
            (165681, '213581', 1, 16.89, 14.2, 3.9, 3.28, '2013-04-08 17:39:30', 0, 17, 5, '', '', '', '', 0, 0, '', '', '', NULL, '', '2', 9, 'EUR', 1, 1, '172.16.10.71');

            INSERT IGNORE INTO `s_order_details` (`id`, `orderID`, `ordernumber`, `articleID`, `articleordernumber`, `price`, `quantity`, `name`, `status`, `shipped`, `shippedgroup`, `releasedate`, `modus`, `esdarticle`, `taxID`, `tax_rate`, `config`) VALUES
            (1531352, 165681, '213581', 12, 'SW10012', 9.99, 1, 'Kobra Vodka 37,5%', 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
            (1531353, 165681, '213581', 0, 'SHIPPINGDISCOUNT', -2, 1, 'Warenkorbrabatt', 0, 0, 0, '0000-00-00', 4, 0, 0, 19, ''),
            (1531354, 165681, '213581', 0, 'sw-surcharge', 5, 1, 'Mindermengenzuschlag', 0, 0, 0, '0000-00-00', 4, 0, 0, 19, '');
        ";

        Shopware()->Db()->query($sql);
    }

    /**
     * Cleaning up testData
     */
    protected function tearDown()
    {
        parent::tearDown();

        $sql = "
            DELETE FROM `s_order` WHERE `id` = 165681;
            DELETE FROM `s_order_details` WHERE `orderID` = 165681;
        ";

        Shopware()->Db()->query($sql);
    }


    /**
     * Checks if the orders are still returned by the method
     */
    public function testAccountGetOrders()
    {
        //login
        $this->Request()
                ->setMethod('POST')
                ->setPost('email', 'test@example.com')
                ->setPost('password', 'shopware');

        $this->dispatch('/account/login');
        $this->assertNotEmpty(Shopware()->Session()->sUserId);

        //call the method
        $orders = Shopware()->Modules()->Admin()->sGetOpenOrderData();
        $this->assertGreaterThan(0, $orders);

        //get the dummyOrder
        $dummyOrder = $this->getDummyOrder($orders);
        $this->assertNotEmpty($dummyOrder);
        $this->assertEquals(213581, $dummyOrder["ordernumber"]);
        $this->assertEquals(1, $dummyOrder["userID"]);
    }

    /**
     * helper to return the dummy order
     */
    private function getDummyOrder($orders)
    {
        foreach ($orders["orderData"] as $order) {
            if ($order["id"] == 165681) {
                return $order;
            }
        }
        return array();
    }
}
