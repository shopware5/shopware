<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 5152
 */
class Shopware_RegressionTests_Ticket5152 extends Enlight_Components_Test_Plugin_TestCase
{    
	/**
     * Test case method
     */
	public function testDbCache()
	{
		$db = Shopware()->Adodb();
		
		$sql = '
			SELECT SQL_CALC_FOUND_ROWS *
			FROM `s_articles`
			LIMIT 10
		';
		$rows = $db->CacheGetAll(100, $sql, false, 'category_3');
		$foundRows = $db->CacheGetFoundRows();
		
		$this->assertEquals(10, count($rows));
		$this->assertGreaterThan(10, $foundRows);
	}
}