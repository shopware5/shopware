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
 *
 */

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_RegressionTests_Ticket4806Test extends Enlight_Components_Test_Controller_TestCase
{
    //order number of the article spachtelmasse
    const ARTICLE_DETAIL_ORDER_NUMBER = 'SW10239';

    /**
     * Set up method to set some values to test
     */
    public function setUp()
    {
        parent::setUp();
        $sql= "UPDATE `s_articles_details` SET `width` = '2.010', `height` = '3.020', `length` = '4.330'  WHERE `ordernumber` = :orderNumber";
        Shopware()->Db()->query($sql, array("orderNumber" => self::ARTICLE_DETAIL_ORDER_NUMBER));
    }

    /**
     * revert the test values
     */
    public function tearDown()
    {
        parent::tearDown();
        $sql= "UPDATE `s_articles_details` SET `width` = NULL, `height` = NULL, `length` = NULL  WHERE `ordernumber` = :orderNumber";
        Shopware()->Db()->query($sql, array("orderNumber" => self::ARTICLE_DETAIL_ORDER_NUMBER));
    }

    /**
     * Tests if width, length and height will be return by the sGetArticleById method
     */
    public function testGetArticleByIdData()
    {
        $sql= "SELECT articleID FROM s_articles_details WHERE ordernumber = :orderNumber";
        $articleId = Shopware()->Db()->fetchOne($sql, array("orderNumber" => self::ARTICLE_DETAIL_ORDER_NUMBER));
        $this->dispatch("/");
        $articleDetailData = Shopware()->Modules()->Articles()->sGetArticleById($articleId);
        $this->assertEquals('2.010', $articleDetailData["width"]);
        $this->assertEquals('4.330', $articleDetailData["length"]);
        $this->assertEquals('3.020', $articleDetailData["height"]);
    }

    /**
     * Tests if width, length and height will be return by the sGetPromotionById method
     */
    public function testGetPromotionById()
    {
        $articlePromotionData = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, self::ARTICLE_DETAIL_ORDER_NUMBER);
        $this->assertEquals('2.010', $articlePromotionData["width"]);
        $this->assertEquals('4.330', $articlePromotionData["length"]);
        $this->assertEquals('3.020', $articlePromotionData["height"]);
    }
}
