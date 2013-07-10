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
class Shopware_Tests_Controllers_Backend_OrderTest extends Enlight_Components_Test_Controller_TestCase
{

    const ORDER_ID = 15315351;
    const ARTICLE_ORDER_NUMBER = 'SW10003';

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
     * test if a position can be added an if the order amount is changed
     */
    public function testSavePosition()
    {
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        $this->assertEquals('126.82',$this->getInvoiceAmount());
        $dummyInstock = $this->getDummyArticleInstock();

        //add position to the order
        $this->Request()
                ->setMethod('POST')
                ->setPost('orderId', self::ORDER_ID)
                ->setPost('quantity', '1')
                ->setPost('articleName', 'Münsterländer Aperitif 16%')
                ->setPost('statusId', '1')
                ->setPost('articleId', '0')
                ->setPost('taxId', '1')
                ->setPost('taxRate', '19')
                ->setPost('articleNumber', self::ARTICLE_ORDER_NUMBER)
                ->setPost('price', '15');

        $this->dispatch('backend/Order/savePosition');
        $returnData = json_decode($this->Response()->getBody());
        $this->assertEquals('141.82',$this->getInvoiceAmount());
        $this->assertEquals('141.82',$returnData->invoiceAmount);
        $this->assertEquals('1',$returnData->success);
        $this->assertNotEmpty($returnData->data->id);
        $dummyInstock = $dummyInstock - 1;
        $this->assertEquals($dummyInstock, $this->getDummyArticleInstock());

        $positionId = $returnData->data->id;

        //change the order position
        $this->Request()
                ->setMethod('POST')
                ->setPost('id', $positionId)
                ->setPost('orderId', self::ORDER_ID)
                ->setPost('quantity', '2')
                ->setPost('articleName', 'Münsterländer Aperitif 16%')
                ->setPost('statusId', '1')
                ->setPost('articleId', '0')
                ->setPost('taxId', '1')
                ->setPost('taxRate', '19')
                ->setPost('articleNumber', self::ARTICLE_ORDER_NUMBER)
                ->setPost('price', '16');

        $this->dispatch('backend/Order/savePosition');

        $returnData = json_decode($this->Response()->getBody());
        $this->assertEquals('158.82',$this->getInvoiceAmount());
        $this->assertEquals('158.82',$returnData->invoiceAmount);
        $this->assertEquals('1',$returnData->success);
        $dummyInstock = $dummyInstock - 1;
        //check if the has been updated
        $this->assertEquals($dummyInstock, $this->getDummyArticleInstock());
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

    /**
     * helper method to return the instock of the dummy article
     *
     * @return string
     */
    private function getDummyArticleInstock() {
        $sql= "SELECT instock FROM s_articles_details WHERE ordernumber = ?";
        return Shopware()->Db()->fetchOne($sql, array(self::ARTICLE_ORDER_NUMBER));
    }
}
