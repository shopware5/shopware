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
class Shopware_RegressionTests_Ticket4887 extends Enlight_Components_Test_Plugin_TestCase
{
    /**
     * Set up test case, fix demo data where needed
     */
    public function setUp()
    {
        parent::setUp();

        // Add price group
        $sql = "
        UPDATE s_articles SET pricegroupActive = 1 WHERE id = 2;
        INSERT INTO s_core_pricegroups_discounts (`groupID`, `customergroupID`, `discount`, `discountstart`) VALUES (1, 1, 5, 1);
        ";

        Shopware()->Db()->query($sql);
    }

    /**
     * Cleaning up testData
     */
    protected function tearDown()
    {
        parent::tearDown();

        // delete price group
        $sql = "
        UPDATE s_articles SET pricegroupActive = 0 WHERE id = 2;
        DELETE FROM s_core_pricegroups_discounts WHERE `customergroupID` = 1 AND `discount` = 5;
        ";
        Shopware()->Db()->query($sql);

    }

    /**
     * Checks if price group is taken into account correctly
     */
    public function testPriceGroupForMainVariant()
    {
        $this->dispatch("/");

        $correctPrice = "18,99";
        $article = Shopware()->Modules()->Articles()->sGetArticleById(
            2
        );
        $this->assertEquals($correctPrice, $article["price"]);
    }


}
