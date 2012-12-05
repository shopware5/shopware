<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 5349
 */
class Shopware_RegressionTests_Ticket5349 extends Enlight_Components_Test_TestCase
{    
    /**
     * Test case method
     */
	public function testGetNewPromotion()
	{
		$articles = Shopware()->Modules()->Articles();
		$articles->sCachePromotions = array();
		$this->assertNotEmpty($articles->sGetPromotionById('new', 3));
		$this->assertNotEmpty($articles->sGetPromotionById('new', 3));
		$this->assertNotEmpty($articles->sGetPromotionById('new', 3));
	}
}