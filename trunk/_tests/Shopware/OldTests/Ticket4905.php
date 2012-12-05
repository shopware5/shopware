<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4905
 */
class Shopware_RegressionTests_Ticket4905 extends Enlight_Components_Test_TestCase
{       	
	/**
     * Test case method
     */
	public function testDeleteOtherArticleImages()
	{
        return;

		$articleId = 3;
		
		$sql = 'SELECT CONCAT(?, `img`, ?, `extension`) as `image` FROM `s_articles_img` WHERE `articleID`=?';
		$image = Shopware()->Db()->fetchOne($sql, array(
			Shopware()->DocPath(trim(str_replace('/', '_', Shopware()->Config()->articleImages), '_')),
			'.',
			$articleId
		));
		
		$result = Shopware()->Api()->Import()->sArticleImages($articleId, array(
	    	$image
	    ));
	    
	    $this->assertNotEmpty($result);
	    $this->assertFileNotExists($image);
	}
}