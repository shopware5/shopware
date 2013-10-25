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
class Shopware_RegressionTests_Ticket5515 extends Enlight_Components_Test_Plugin_TestCase
{

	const ORDER_NUMBER = "SW3133715";
	const ARTICLE_ID = 272;

    /**
     * Checks if the if the main article data update over the old deprecated api works
     */
    public function testDeprecatedAPIArticleUpdate()
    {
        // Clear entitymanager to prevent weird 'model shop not persisted' errors.
        Shopware()->Models()->clear();

	    //initial check for the article data
	    $sql= "SELECT * FROM s_articles WHERE id = ?";
	    $initialArticleData = Shopware()->Db()->fetchRow($sql, array(self::ARTICLE_ID));
	    $this->assertEmpty($initialArticleData["laststock"]);

	    //update the laststock via api
	    $articleData = array(
		    "ordernumber" => "SW10239",
		    "laststock" => "1",
	    );

	    $config = array("update" => true);
        $result = Shopware()->Api()->Import()->sArticle($articleData, $config);
	    $this->assertNotEmpty($result);


	    //check that only the laststock value has changed
	    $sql= "SELECT * FROM s_articles WHERE id = ?";
	    $newArticleData = Shopware()->Db()->fetchRow($sql, array(self::ARTICLE_ID));
	    $this->assertEquals(1,$newArticleData["laststock"]);

	    //reset the laststock to check that no other value has been changed excluded the changetime
	    $newArticleData["laststock"] = 0;
	    $newArticleData["changetime"] = $initialArticleData["changetime"];
	    $this->assertEquals($initialArticleData,$newArticleData);

    }

	/**
	 * Checks if the if the main article data insert over the old deprecated api works
	 */
	public function testDeprecatedAPIArticleInsert()
	{

		$articleData = array(
	        "ordernumber" => self::ORDER_NUMBER,
	        "mainID" => 0,
	        "maindetailsID" => 0,
	        "name" => 'Spachtelmasse New',
	        "description_long" => 'Description',
	        "shippingfree" => 0,
	        "notification" => 0,
			"laststock" => 1,
	        "topseller" => 0,
	        "mode" => 0,
	        "pricegroupActive" => 0,
	        "impressions" => 0,
	        "sales" => 0,
	        "active" => 1,
	        "kind" => 1,
	        "instock" => 0,
	        "stockmin" => 0,
	        "supplier" => 'The Deli Garage',
	        "tax" => '19.00'
	);
		$result = Shopware()->Api()->Import()->sArticle($articleData);
		$this->assertNotEmpty($result);


		//check that only the laststock value has changed
		$sql= "SELECT * FROM s_articles WHERE id = ?";
		$newArticleData = Shopware()->Db()->fetchRow($sql, array($result['articleID']));
		$this->assertEquals($articleData["laststock"],$newArticleData["laststock"]);
		$this->assertEquals($articleData["name"],$newArticleData["name"]);
		$this->assertEquals($articleData["description_long"],$newArticleData["description_long"]);


    }

	/**
	 * Cleaning up testData
	 */
	protected function tearDown()
	{
		parent::tearDown();
		$sql= "UPDATE `s_articles` SET `laststock` = '0' WHERE `id` =?";
		Shopware()->Db()->query($sql, array(self::ARTICLE_ID));
		Shopware()->Api()->Import()->sDeleteArticle(array("ordernumber"=>self::ORDER_NUMBER));
	}
}
