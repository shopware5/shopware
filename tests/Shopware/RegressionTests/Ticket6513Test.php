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
class Shopware_RegressionTests_Ticket6513 extends Enlight_Components_Test_Controller_TestCase
{

    const ORDER_ID = 15315351;

    /**
     * Set up test case, fix demo data where needed
     */
    public function setUp()
    {
        parent::setUp();

        // insert test order
        $sql = "
              INSERT IGNORE INTO `s_order` (`id`, `ordernumber`, `userID`, `invoice_amount`, `invoice_amount_net`, `invoice_shipping`, `invoice_shipping_net`, `ordertime`, `status`, `cleared`, `paymentID`, `transactionID`, `comment`, `customercomment`, `internalcomment`, `net`, `taxfree`, `partnerID`, `temporaryID`, `referer`, `cleareddate`, `trackingcode`, `language`, `dispatchID`, `currency`, `currencyFactor`, `subshopID`, `remote_addr`) VALUES
              (:orderId, '29996', 1, 126.82, 106.57, 3.9, 3.28, '2013-07-10 08:17:20', 0, 17, 5, '', '', '', '', 0, 0, '', '', '', NULL, '', '1', 9, 'EUR', 1, 1, '172.16.10.71');

              INSERT IGNORE INTO `s_order_details` (`id`, `orderID`, `ordernumber`, `articleID`, `articleordernumber`, `price`, `quantity`, `name`, `status`, `shipped`, `shippedgroup`, `releasedate`, `modus`, `esdarticle`, `taxID`, `tax_rate`, `config`) VALUES
              (15315352, :orderId, '20003', 178, 'SW10178', 19.95, 1, 'Strandtuch Ibiza', 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
              (15315353, :orderId, '20003', 177, 'SW10177', 34.99, 1, 'Strandtuch Stripes für Kinder', 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
              (15315354, :orderId, '20003', 173, 'SW10173', 39.99, 1, 'Strandkleid Flower Power', 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
              (15315355, :orderId, '20003', 160, 'SW10160.1', 29.99, 1, 'Sommer Sandale Ocean Blue 36', 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
              (15315356, :orderId, '20003', 0, 'SHIPPINGDISCOUNT', -2, 1, 'Warenkorbrabatt', 0, 0, 0, '0000-00-00', 4, 0, 0, 19, '');
        ";

        Shopware()->Db()->query($sql,array("orderId" => self::ORDER_ID));
    }

    /**
     * Cleaning up testData
     */
    protected function tearDown()
    {
        parent::tearDown();

        $sql = "
            DELETE FROM `s_order` WHERE `id` = :orderId;
            DELETE FROM `s_order_details` WHERE `orderID` = :orderId;
        ";

        Shopware()->Db()->query($sql,array("orderId" => self::ORDER_ID));
    }

    /**
     * test to delete the position
     */
    public function testDeletePosition()
    {
        $this->assertEquals('126.82', $this->getInvoiceAmount());
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();

        //delete the order position
        $this->Request()
                ->setMethod('POST')
                ->setPost('id', '15315352')
                ->setPost('orderID', self::ORDER_ID);
        $this->dispatch('backend/Order/deletePosition');
        $this->assertEquals('106.87', $this->getInvoiceAmount());
    }



    /**
     * Helper method to return the order amount
     *
     * @return string
     */
    private function getInvoiceAmount() {
        $sql= "SELECT invoice_amount FROM s_order WHERE id = ?";
        return Shopware()->Db()->fetchOne($sql, array(self::ORDER_ID));
    }

}
