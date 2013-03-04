<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_RegressionTests_Ticket4758 extends Enlight_Components_Test_Controller_TestCase
{

    const MAIN_VARIANT_ID = 444;
    const VARIANT_ID = 445;
    /**
     * Set up test case, add the pricegroup
     */
    public function setUp()
    {
        parent::setUp();

        // Create Pricegroup
        $sql = "
            REPLACE INTO s_core_pricegroups_discounts (`groupID`, `customergroupID`, `discount`, `discountstart`) VALUES
            (1, 1, 5, 1),
            (1, 1, 10, 2),
            (1, 1, 20, 3),
            (1, 1, 25, 4);
        ";
        Shopware()->Db()->query($sql);
    }


    /**
     * Tests the prices with a main variant article
     */
    public function testMainVariantArticlePriceGroups()
    {
        $this->dispatch('/beispiele/konfiguratorartikel/202/artikel-mit-standardkonfigurator?c=22');
        $articleData = $this->View()->getAssign('sArticle');
        //check prices for configurator article without pricegroup and without stapping
        $this->assertEquals(0,$articleData["pricegroupID"]);
        $this->assertEquals(0,$articleData["pricegroupActive"]);
        $this->assertTrue(empty($articleData["sBlockPrices"]));
        $this->assertEquals('20,99',$articleData["price"]);


        //check prices for configurator article with pricegroup and without stapping
        $sql = "UPDATE s_articles SET pricegroupActive = 1, pricegroupID = 1 WHERE id = 202";
        Shopware()->Db()->query($sql, array());

        $this->dispatch('/beispiele/konfiguratorartikel/202/artikel-mit-standardkonfigurator?c=22');
        $articleData = $this->View()->getAssign('sArticle');
        $this->assertEquals(1,$articleData["pricegroupID"]);
        $this->assertEquals(1,$articleData["pricegroupActive"]);
        $this->assertPriceGroupBlockPrices($articleData);


        //set stapping prices to the main variant with the detail id 444
        $this->insertPriceStapping(self::MAIN_VARIANT_ID);

        //check prices for configurator article with pricegroup and with stapping
        //the stapping shouldn't have any effect because of the pricegroup
        $this->dispatch('/beispiele/konfiguratorartikel/202/artikel-mit-standardkonfigurator?c=22');
        $articleData = $this->View()->getAssign('sArticle');

        //assert the same things because the pricegroup setting has a higher priority than the stapping prices
        $this->assertEquals(1,$articleData["pricegroupID"]);
        $this->assertEquals(1,$articleData["pricegroupActive"]);
        $this->assertPriceGroupBlockPrices($articleData);

        //check prices for the  article with stapping but without the pricegroup
        $sql= "UPDATE s_articles SET pricegroupActive = 0 WHERE id = 202";
        Shopware()->Db()->exec($sql);

        $this->dispatch('/beispiele/konfiguratorartikel/202/artikel-mit-standardkonfigurator?c=22');
        $articleData = $this->View()->getAssign('sArticle');
        $this->assertEquals(1,$articleData["pricegroupID"]);
        $this->assertEquals(0,$articleData["pricegroupActive"]);
        $this->assertStappingPrices($articleData);
    }

    /**
     * Tests the prices with a variant article
     */
    public function testVariantArticlePriceGroups()
    {
        //reset the test data
       $this->resetArticleData(self::MAIN_VARIANT_ID);

        $this->Request()
                ->setMethod('POST')
                ->setPost('group', array(
            6 => 15,
            7 => 63,
        ));

        $this->dispatch('/beispiele/konfiguratorartikel/202/artikel-mit-standardkonfigurator?c=22');
        $articleData = $this->View()->getAssign('sArticle');

        //check prices for configurator article without pricegroup and without stapping
        $this->assertEquals(0,$articleData["pricegroupID"]);
        $this->assertEquals(0,$articleData["pricegroupActive"]);
        $this->assertTrue(empty($articleData["sBlockPrices"]));
        $this->assertEquals('20,99',$articleData["sConfiguratorSelection"]["price"][0]["price"]);
        $this->assertEquals(1,count($articleData["sConfiguratorSelection"]["price"]));

        //check prices for configurator article with pricegroup and without stapping
        $sql = "UPDATE s_articles SET pricegroupActive = 1, pricegroupID = 1 WHERE id = 202";
        Shopware()->Db()->query($sql, array());
        $this->dispatch('/beispiele/konfiguratorartikel/202/artikel-mit-standardkonfigurator?c=22');
        $articleData = $this->View()->getAssign('sArticle');
        $this->assertEquals(1,$articleData["pricegroupID"]);
        $this->assertEquals(1,$articleData["pricegroupActive"]);
        $this->assertPriceGroupBlockPrices($articleData);

        //set stapping prices to the  variant with the detail id 445
        $this->insertPriceStapping(self::VARIANT_ID);

        //check prices for configurator article with pricegroup and with stapping
        //the stapping shouldn't have any effect because of the pricegroup
        $this->dispatch('/beispiele/konfiguratorartikel/202/artikel-mit-standardkonfigurator?c=22');
        $articleData = $this->View()->getAssign('sArticle');

        //assert the same things because the pricegroup setting has a higher priority than the stapping prices
        $this->assertEquals(1,$articleData["pricegroupID"]);
        $this->assertEquals(1,$articleData["pricegroupActive"]);
        $this->assertPriceGroupBlockPrices($articleData);

        //check prices for the article with stapping but without the pricegroup
        $sql= "UPDATE s_articles SET pricegroupActive = 0 WHERE id = 202";
        Shopware()->Db()->exec($sql);

        $this->dispatch('/beispiele/konfiguratorartikel/202/artikel-mit-standardkonfigurator?c=22');
        $articleData = $this->View()->getAssign('sArticle');
        $this->assertEquals(1,$articleData["pricegroupID"]);
        $this->assertEquals(0,$articleData["pricegroupActive"]);
        $this->assertStappingPrices($articleData);
    }

    /**
     * helper method to resets the article data
     */
    private function resetArticleData($articleDetailsId) {
        // delete price group
        $sql = "UPDATE s_articles SET pricegroupActive = 0, pricegroupID = 0 WHERE id = 202;";
        Shopware()->Db()->query($sql);

        //reset the stapping prices
        $sql= "DELETE FROM`s_articles_prices` WHERE `articleID` = 202 AND articledetailsID = ?";
        Shopware()->Db()->query($sql, array($articleDetailsId));

        $sql= "REPLACE INTO `s_articles_prices` (`pricegroup`, `from`, `to`, `articleID`, `articledetailsID`, `price`, `pseudoprice`, `baseprice`, `percent`) VALUES
        ('EK', 1, 'beliebig', 202, ?, 17.638655462185, 0, 0, '0.00')";
        Shopware()->Db()->query($sql, array($articleDetailsId));
    }

    /**
     * helper method to insert the prices stapping
     */
    private function insertPriceStapping($articleDetailId) {
        $sql= "
        DELETE FROM`s_articles_prices` WHERE `articleID` = 202 AND articledetailsID = :articleDetailId;
        REPLACE INTO `s_articles_prices` (`pricegroup`, `from`, `to`, `articleID`, `articledetailsID`, `price`, `pseudoprice`, `baseprice`, `percent`) VALUES
        ('EK', 1, '2', 202, :articleDetailId, 17.638655462185, 0, 0, '0.00'),
        ('EK', 3, '4', 202, :articleDetailId, 15.126050420168, 0, 0, '5.26'),
        ('EK', 5, '6', 202, :articleDetailId, 14.285714285714, 0, 0, '10.53'),
        ('EK', 7, '10', 202, :articleDetailId, 13.445378151261, 0, 0, '15.79'),
        ('EK', 11, 'beliebig', 202, :articleDetailId, 12.605042016807, 0, 0, '21.05');";
        Shopware()->Db()->query($sql, array('articleDetailId' => $articleDetailId));
    }

    /**
     * helper method to assert the block Prices
     * @param $articleData
     */
    private function assertPriceGroupBlockPrices($articleData) {
        $this->assertFalse(empty($articleData["sBlockPrices"]));
        $this->assertEquals('19,94',$articleData["sBlockPrices"][0]["price"]);
        $this->assertEquals('18,89',$articleData["sBlockPrices"][1]["price"]);
        $this->assertEquals('16,79',$articleData["sBlockPrices"][2]["price"]);
        $this->assertEquals('15,74',$articleData["sBlockPrices"][3]["price"]);
        $this->assertEquals(4,count($articleData["sBlockPrices"]));
    }

    /**
     * helper method to assert the stapping prices
     * @param $articleData
     */
    private function assertStappingPrices($articleData) {
        $articleBlockPrices = $articleData["sConfiguratorSelection"]["sBlockPrices"];
        $this->assertFalse(empty($articleData["sBlockPrices"]));
        $this->assertEquals('20,99',$articleBlockPrices[0]["price"]);
        $this->assertEquals('18,00',$articleBlockPrices[1]["price"]);
        $this->assertEquals('17,00',$articleBlockPrices[2]["price"]);
        $this->assertEquals('16,00',$articleBlockPrices[3]["price"]);
        $this->assertEquals('15,00',$articleBlockPrices[4]["price"]);
        $this->assertEquals(5,count($articleBlockPrices));
    }



    /**
    * Cleaning up testData
    */
    protected function tearDown()
    {
        parent::tearDown();

        // delete price group
        $sql = "
        UPDATE s_articles SET pricegroupActive = 0, pricegroupID = 0 WHERE id = 202;
        DELETE FROM s_core_pricegroups_discounts WHERE `customergroupID` = 1 AND groupID = 1;
        ";
        Shopware()->Db()->query($sql);

        //reset the stapping prices for the main variant
        $sql= "DELETE FROM`s_articles_prices` WHERE `articleID` = 202 AND articledetailsID = ?";
        Shopware()->Db()->query($sql, array(self::MAIN_VARIANT_ID));

        $sql= "REPLACE INTO `s_articles_prices` (`pricegroup`, `from`, `to`, `articleID`, `articledetailsID`, `price`, `pseudoprice`, `baseprice`, `percent`) VALUES
        ('EK', 1, 'beliebig', 202, ?, 17.638655462185, 0, 0, '0.00')";
        Shopware()->Db()->query($sql, array(self::MAIN_VARIANT_ID));

        //reset the stapping prices for the variant
        $sql= "DELETE FROM`s_articles_prices` WHERE `articleID` = 202 AND articledetailsID = ?";
        Shopware()->Db()->query($sql, array(self::VARIANT_ID));
        $sql= "REPLACE INTO `s_articles_prices` (`pricegroup`, `from`, `to`, `articleID`, `articledetailsID`, `price`, `pseudoprice`, `baseprice`, `percent`) VALUES
        ('EK', 1, 'beliebig', 202, ?, 17.638655462185, 0, 0, '0.00')";
        Shopware()->Db()->query($sql, array(self::VARIANT_ID));
    }

}
