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
    /**
     * Set up test case, fix demo data where needed
     */
    public function setUp()
    {
        parent::setUp();

        // Create Pricegroup and add it to the articles
        $sql = "
            INSERT INTO s_core_pricegroups_discounts (`groupID`, `customergroupID`, `discount`, `discountstart`) VALUES
            (1, 1, 5, 1),
            (1, 1, 10, 2),
            (1, 1, 20, 3),
            (1, 1, 25, 4);
        ";

        Shopware()->Db()->query($sql);
    }


//todo@ms main variante sollte hier ausreichen zu testen
    /**
     * Test case method
     *
     * Konfigurator Artikel mit Preisgruppe
     * Konfigurator Artikel ohne Preisgruppe
     * Konfigurator Artikel mit Staffelpreise
     * Konfigurator Artikel mit Staffelpreise und Preisgruppe
     */
    public function testConfiguratorArticlePriceGroups()
    {
        $module = Shopware()->Modules()->Articles();
        //check prices for configurator article without pricegroup and without stapping
        $this->dispatch("/");
        $articleData = $module->sGetArticleById(202);
        $this->assertEquals(0,$articleData["pricegroupID"]);
        $this->assertEquals(0,$articleData["pricegroupActive"]);
        $this->assertTrue(empty($articleData["sBlockPrices"]));
        $this->assertEquals('20,99',$articleData["sConfiguratorSelection"]["price"][0]["price"]);


        //check prices for configurator article with pricegroup and without stapping
        $sql = "UPDATE s_articles SET pricegroupActive = 1, pricegroupID = 1 WHERE id = 202";
        Shopware()->Db()->query($sql, array());

        $this->dispatch("/");
        $articleData = $module->sGetArticleById(202);
        $this->assertEquals(1,$articleData["pricegroupID"]);
        $this->assertEquals(1,$articleData["pricegroupActive"]);
        $this->assertFalse(empty($articleData["sBlockPrices"]));
        $this->assertEquals('19,94',$articleData["sBlockPrices"][0]["price"]);
        $this->assertEquals('18,89',$articleData["sBlockPrices"][1]["price"]);
        $this->assertEquals('16,79',$articleData["sBlockPrices"][2]["price"]);
        $this->assertEquals('15,74',$articleData["sBlockPrices"][3]["price"]);
        $this->assertEquals(4,count($articleData["sBlockPrices"]));


        //todo@ms: easily change to the main variant
        //set stapping prices to the variant with the detail id 445
//        $sql= "INSERT INTO `s_articles_prices` (`pricegroup`, `from`, `to`, `articleID`, `articledetailsID`, `price`, `pseudoprice`, `baseprice`, `percent`) VALUES
//            ('EK', 1, '2', 202, 445, 17.638655462185, 0, 0, '0.00'),
//            ('EK', 3, '4', 202, 445, 11.764705882353, 0, 0, '33.30'),
//            ('EK', 5, 'beliebig', 202, 445, 8.4033613445378, 0, 0, '52.36');";
//        Shopware()->Db()->query($sql);
//
//        //check prices for configurator article with pricegroup and with stapping
//        //the stapping shouldn't have any effekt because of the pricegroup
//
////        $this->Request()->setMethod('POST')->setPost('group', array(6=>15,7 =>63));
//
//        $module->sSYSTEM->_POST = array('group' => array(6=>15,7 =>63));
//        //$this->dispatch("/");
//        $articleData = $module->sGetArticleById(202);
//
//
//        $this->assertEquals(1,$articleData["pricegroupID"]);
//        $this->assertEquals(1,$articleData["pricegroupActive"]);
//        $this->assertFalse(empty($articleData["sBlockPrices"]));
//        $this->assertEquals('19,94',$articleData["sBlockPrices"][0]["price"]);
//        $this->assertEquals('18,89',$articleData["sBlockPrices"][1]["price"]);
//        $this->assertEquals('16,79',$articleData["sBlockPrices"][2]["price"]);
//        $this->assertEquals('15,74',$articleData["sBlockPrices"][3]["price"]);
//        $this->assertEquals(4,count($articleData["sBlockPrices"]));


    }

    /**
     *
     * Artikel mit aktiver Preisgruppe
     * Artikel ohne Preisgruppe
     * Artikel mit Staffelpreise und Preisgruppe
     */
    public function testArticlePriceGroups()
    {
        /**
         * Testcases
         *
         * Artikel mit aktiver Preisgruppe
         * Artikel ohne Preisgruppe
         * Artikel mit Staffelpreise und Preisgruppe
         *
         *
         *
         *
         *
         */

//        $this->dispatch("/");
//        $articleData = Shopware()->Modules()->Articles()->sGetArticleById(202);
//        echo "<pre>";
//        print_r($articleData);
//        echo "</pre>";
//        exit();


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
        //todo@ms: delete variant prices for detailID 445
        Shopware()->Db()->query($sql);

    }

}
